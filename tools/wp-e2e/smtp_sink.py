#!/usr/bin/env python3
"""A tiny SMTP server that keeps every message it receives as a numbered .eml file (and one line of JSON per message).
Usage: smtp_sink.py <port> <outdir>"""
import socketserver, sys, os, json, time, re

PORT, OUT = int(sys.argv[1]), sys.argv[2]
os.makedirs(OUT, exist_ok=True)
COUNT = [len([f for f in os.listdir(OUT) if f.endswith('.eml')])]


class H(socketserver.StreamRequestHandler):
    def send(self, s):
        self.wfile.write((s + '\r\n').encode()); self.wfile.flush()

    def handle(self):
        self.send('220 sink ESMTP')
        env = {'from': '', 'to': []}
        while True:
            line = self.rfile.readline()
            if not line:
                return
            cmd = line.decode('utf-8', 'replace').strip()
            up = cmd.upper()
            if up.startswith(('EHLO', 'HELO')):
                self.send('250-sink'); self.send('250 8BITMIME')
            elif up.startswith('MAIL FROM'):
                env = {'from': re.sub(r'.*<(.*)>.*', r'\1', cmd), 'to': []}; self.send('250 ok')
            elif up.startswith('RCPT TO'):
                env['to'].append(re.sub(r'.*<(.*)>.*', r'\1', cmd)); self.send('250 ok')
            elif up == 'DATA':
                self.send('354 end with .')
                data = []
                while True:
                    l = self.rfile.readline()
                    if l in (b'.\r\n', b'.\n', b''):
                        break
                    data.append(l[1:] if l.startswith(b'..') else l)
                COUNT[0] += 1
                n = COUNT[0]
                raw = b''.join(data)
                open(os.path.join(OUT, '%03d.eml' % n), 'wb').write(raw)
                with open(os.path.join(OUT, 'log.jsonl'), 'a') as f:
                    f.write(json.dumps({'n': n, 'time': time.strftime('%H:%M:%S'), 'envelope_from': env['from'], 'envelope_to': env['to']}) + '\n')
                self.send('250 queued as %d' % n)
            elif up == 'QUIT':
                self.send('221 bye'); return
            elif up in ('RSET', 'NOOP'):
                self.send('250 ok')
            else:
                self.send('250 ok')


class S(socketserver.ThreadingTCPServer):
    allow_reuse_address = True
    daemon_threads = True


S(('127.0.0.1', PORT), H).serve_forever()
