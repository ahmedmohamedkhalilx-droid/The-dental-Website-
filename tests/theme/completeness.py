#!/usr/bin/env python3
"""
Completeness check: every text string found on each page of the old site must appear in the same
page of the new theme, unless it is listed in expected-deviations.json with a reason.

Usage: python3 completeness.py <rendered-dir>
"""
import sys, json, re, html, os

here = os.path.dirname(os.path.abspath(__file__))
src = json.load(open(here + '/fixtures/source-strings.json'))
dev = json.load(open(here + '/expected-deviations.json')) if os.path.exists(here + '/expected-deviations.json') else {}
out = sys.argv[1]

# Old page slug -> rendered file
FILES = {'home': 'index.html'}


def norm(s):
    s = html.unescape(s)
    s = s.replace('’', "'").replace('‘', "'").replace('“', '"').replace('”', '"').replace('–', '-').replace('—', '-').replace('\xa0', ' ')
    s = re.sub(r'\s+', ' ', s).strip().lower()
    return s


def page_text(path):
    h = open(path, encoding='utf-8').read()
    h = re.sub(r'<(script|style)[^>]*>.*?</\1>', ' ', h, flags=re.S)
    h = re.sub(r'<[^>]+>', ' ', h)
    return norm(h)


missing_total = 0
checked = 0
for slug, strings in src.items():
    path = os.path.join(out, FILES.get(slug, slug + '.html'))
    if not os.path.exists(path):
        print(f'MISSING PAGE  {slug}: {path} was not rendered')
        missing_total += 1
        continue
    text = page_text(path)
    allowed = {norm(d['string']): d['reason'] for d in dev.get(slug, [])}
    allowed.update({norm(d['string']): d['reason'] for d in dev.get('*', [])})
    miss = []
    for s in strings:
        n = norm(s)
        checked += 1
        if n in text or n in allowed:
            continue
        miss.append(s)
    if miss:
        missing_total += len(miss)
        print(f'--- {slug}: {len(miss)} string(s) from the old page are not in the new page')
        for m in miss:
            print('    ', json.dumps(m, ensure_ascii=False)[:230])
print(f'\nchecked {checked} strings across {len(src)} old pages: {missing_total} not accounted for')
sys.exit(1 if missing_total else 0)
