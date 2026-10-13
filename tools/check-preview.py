#!/usr/bin/env python3
"""
Checks the built preview: every page exists, and every link, stylesheet, script and image that points
inside the site (under the base path) resolves to a file.

Usage: python3 tools/check-preview.py <root-dir> <base-path>
"""
import sys, json, os, re
from html.parser import HTMLParser
from urllib.parse import urlparse, unquote

root, base = sys.argv[1], sys.argv[2].rstrip('/')
slugs = ['home'] + json.load(open(os.path.join(root, 'tools/preview/pages.json')))


class P(HTMLParser):
    def __init__(self):
        super().__init__(); self.urls = []
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        for k in ('href', 'src', 'poster'):
            if a.get(k):
                self.urls.append((tag, a[k]))
        if tag == 'source' and a.get('src'):
            self.urls.append((tag, a['src']))


def target(url):
    u = urlparse(url)
    if u.scheme or u.netloc:
        return None
    path = unquote(u.path)
    if not path.startswith(base + '/') and path != base:
        return None
    rel = path[len(base):].lstrip('/')
    if rel == '' or path.endswith('/'):
        rel = os.path.join(rel, 'index.html')
    return rel


problems, files, links = [], 0, 0
pages = ['index.html' if s == 'home' else f'{s}/index.html' for s in slugs] + ['404.html']
for page in pages:
    path = os.path.join(root, page)
    if not os.path.exists(path):
        problems.append(f'{page}: page was not built')
        continue
    files += 1
    p = P(); p.feed(open(path, encoding='utf-8').read())
    for tag, url in p.urls:
        t = target(url)
        if t is None:
            if url.startswith('/') and not url.startswith('//'):
                problems.append(f'{page}: {tag} points outside the site base: {url}')
            continue
        links += 1
        if not os.path.exists(os.path.join(root, t)):
            problems.append(f'{page}: {tag} {url} -> {t} does not exist')
print(f'checked {files} pages, {links} internal links/assets: {len(problems)} problem(s)')
for x in problems[:40]:
    print('  ', x)
sys.exit(1 if problems else 0)
