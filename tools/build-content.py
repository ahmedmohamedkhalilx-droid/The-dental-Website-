#!/usr/bin/env python3
"""
Builds wordpress-theme/the-dental-hauz/inc/content.php from a WordPress export (WXR) of the old site.

Usage: python3 tools/build-content.py <export.xml> <out-dir>
Writes: <out-dir>/content.php   (page copy used by the theme)
        <out-dir>/source-strings.json (every text string found on each old page, for the completeness check)

Only page content is read. Form submissions ("metform-entry") in the export contain patient data and are ignored.
"""
import sys, json, re, html
import xml.etree.ElementTree as ET

NS = {'wp': 'http://wordpress.org/export/1.2/'}


def load(path):
    ch = ET.parse(path).getroot().find('channel')
    items = {}
    for it in ch.findall('item'):
        if it.findtext('wp:post_type', namespaces=NS) != 'page':
            continue
        meta = {m.findtext('wp:meta_key', namespaces=NS): m.findtext('wp:meta_value', namespaces=NS) for m in it.findall('wp:postmeta', NS)}
        items[it.findtext('wp:post_name', namespaces=NS)] = dict(
            id=int(it.findtext('wp:post_id', namespaces=NS)),
            title=html.unescape(it.findtext('title') or ''),
            data=json.loads(meta['_elementor_data']) if meta.get('_elementor_data') else [],
            meta=meta)
    return items


def flat(els, acc=None):
    acc = [] if acc is None else acc
    for x in els:
        if x.get('elType') == 'widget':
            acc.append(x)
        flat(x.get('elements', []), acc)
    return acc


def text(s):
    s = html.unescape(re.sub(r'<br\s*/?>', ' ', s or ''))
    s = re.sub(r'<[^>]+>', '', s)
    return re.sub(r'\s+', ' ', s).strip()


def paras(s):
    s = re.sub(r'</p>|<br\s*/?>', '\n', s or '')
    s = html.unescape(re.sub(r'<[^>]+>', '', s))
    return [re.sub(r'\s+', ' ', p).strip() for p in re.split(r'\n+', s) if p.strip()]


def lines(s):
    return [re.sub(r'\s+', ' ', p).strip() for p in re.split(r'\n+', html.unescape(s or '')) if p.strip()]


def img_id(w, key='image'):
    v = w['settings'].get(key) or {}
    return int(v['id']) if v.get('id') else None


def slug_of(url):
    m = re.search(r'thedentalhauz\.com/([^/]+)/?$', url or '')
    return m.group(1) if m else None


class Page:
    def __init__(self, page):
        self.ws = flat(page['data'])
        self.page = page

    def find(self, wtype, key, value, start=0):
        for i in range(start, len(self.ws)):
            w = self.ws[i]
            if w['widgetType'] == wtype and text(w['settings'].get(key, '')) == value:
                return i
        raise KeyError((wtype, key, value))

    def heading(self, value, start=0):
        return self.find('heading', 'title', value, start)

    def after(self, i, wtype, n=1):
        out = []
        for w in self.ws[i + 1:]:
            if w['widgetType'] == wtype:
                out.append(w)
                if len(out) == n:
                    return out
        raise KeyError((i, wtype))


FIXES = {'Modern Anesthesic': 'Modern Anesthetic'}


def fix(o):
    if isinstance(o, str):
        for a, b in FIXES.items():
            o = o.replace(a, b)
        return o
    if isinstance(o, list):
        return [fix(x) for x in o]
    if isinstance(o, dict):
        return {k: fix(v) for k, v in o.items()}
    return o


def build(items):
    C = {}
    # ---------- HOME ----------
    h = Page(items['home'])
    ws = h.ws
    i = h.heading('Healthy. Everyday.')
    hero = dict(
        title=text(ws[i]['settings']['title']),
        subtitle=text(ws[i + 1]['settings']['title']),
        body=paras(h.after(i, 'text-editor')[0]['settings']['editor'])[0],
        button=text(h.after(i, 'button')[0]['settings']['text']),
        button_link=slug_of(h.after(i, 'button')[0]['settings']['link']['url']),
        image=img_id(h.after(i, 'image')[0]))
    i = h.heading('Helping Patients From Around the Globe!!')
    box = h.after(i, 'icon-box')[0]
    intro = dict(
        eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']),
        video_label=text(box['settings']['title_text']),
        video_url_id=684, video_poster=691,
        paragraphs=[paras(w['settings']['editor'])[0] for w in h.after(i, 'text-editor', 2)],
        button=text(h.after(i, 'button')[0]['settings']['text']),
        button_link=slug_of(h.after(i, 'button')[0]['settings']['link']['url']))
    i = h.heading('Our Services')
    services = []
    j = i
    for n in range(10):
        j = h.find('heading', 'title', '%02d' % (n + 1), j + 1)
        ib = h.after(j, 'image-box')[0]
        bt = h.after(j, 'button')[0]
        services.append(dict(
            num='%02d' % (n + 1), image=img_id(ib), title=text(ib['settings']['title_text']),
            summary=text(ib['settings']['description_text']),
            button=text(bt['settings']['text']), slug=slug_of(bt['settings']['link']['url'])))
    services_head = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))
    call_i = h.find('button', 'text', 'Call Us')
    cta = dict(call=text(ws[call_i]['settings']['text']), call_link=ws[call_i]['settings']['link']['url'],
               msg=text(ws[call_i + 1]['settings']['text']), msg_link=ws[call_i + 1]['settings']['link']['url'])
    i = h.heading('Healthcare Programs')
    lists = h.after(i, 'icon-list', 2)
    programs = dict(
        image=img_id(h.after(i - 2, 'image')[0]) if False else 128,
        eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']),
        body=paras(h.after(i, 'text-editor')[0]['settings']['editor'])[0],
        items=[text(r['text']) for l in lists for r in l['settings']['icon_list']],
        button=text(h.after(i, 'button')[0]['settings']['text']), button_link=h.after(i, 'button')[0]['settings']['link']['url'])
    i = h.heading('Trusted by Our Happy Patients')
    reviews = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))
    i = h.heading('Solution For Your Smile!')
    imgs = [img_id(w) for w in ws[i:] if w['widgetType'] == 'image'][:4]
    smile = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']),
                 paragraphs=[paras(w['settings']['editor'])[0] for w in h.after(i, 'text-editor', 2)],
                 button=text(h.after(i, 'button')[0]['settings']['text']), button_link=slug_of(h.after(i, 'button')[0]['settings']['link']['url']),
                 images=imgs)
    i = h.heading('The Right Device For You!')
    boxes = h.after(i, 'icon-box', 3)
    devices = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']),
                   items=[[text(b['settings']['title_text']), text(b['settings']['description_text'])] for b in boxes],
                   closing=paras(h.after(i, 'text-editor')[0]['settings']['editor'])[0])
    i = h.heading('Frequently Asked Questions?')
    acc = h.after(i, 'elementskit-accordion')[0]['settings']['ekit_accordion_items']
    faq_home = dict(title=text(ws[i]['settings']['title']), image=248,
                    items=[[text(a['acc_title']), text(a['acc_content'])] for a in acc])
    i = h.heading('Book An Appointment')
    book = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))
    C['home'] = dict(hero=hero, intro=intro, services_head=services_head, cta=cta, programs=programs, reviews=reviews,
                     smile=smile, devices=devices, faq=faq_home, book=book)
    C['services'] = services

    # ---------- ABOUT ----------
    a = Page(items['about-us']); ws = a.ws
    i = a.heading('Solution For Your Smile!')
    heads = [text(ws[i + k]['settings']['title']) for k in range(3)]
    boxes = a.after(i, 'icon-box', 2)
    about = dict(
        eyebrow=heads[0], title=heads[1], lead=heads[2],
        intro=paras(a.after(i, 'text-editor')[0]['settings']['editor'])[0],
        images=[304, 305],
        boxes=[[text(b['settings']['title_text']), text(b['settings']['description_text'])] for b in boxes])
    i = a.heading('Our Mission')
    about['mission'] = dict(eyebrow='Our Mission', title=text(ws[i + 1]['settings']['title']),
                            paragraphs=paras(a.after(i, 'text-editor')[0]['settings']['editor']), image=306)
    i = a.heading('High-Quality Dental Aesthetics')
    boxes = a.after(i, 'icon-box', 2)
    about['aesthetics'] = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']),
                               body=paras(a.after(i, 'text-editor')[0]['settings']['editor'])[0], image=310,
                               boxes=[[text(b['settings']['title_text']), text(b['settings']['description_text'])] for b in boxes])
    i = a.heading('Our Accomplishments!')
    ctr = [w for w in ws[i:] if w['widgetType'] == 'counter'][:4]
    about['stats'] = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']),
                          items=[[int(c['settings']['ending_number']), c['settings'].get('suffix', ''), text(c['settings']['title'])] for c in ctr])
    i = a.heading('Trusted by Our Happy Patients')
    about['reviews'] = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))
    C['about'] = about

    # ---------- CONTACT ----------
    c = Page(items['contact-us']); ws = c.ws
    i = c.heading('Get in Touch with Us')
    cards = [w for w in ws[i:] if w['widgetType'] == 'image-box'][:4]
    contact = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']),
                   cards=[dict(num=str(n + 1).zfill(2), image=img_id(b), title=text(b['settings']['title_text']),
                               lines=lines(b['settings']['description_text']),
                               link=(b['settings'].get('link') or {}).get('url', '')) for n, b in enumerate(cards)])
    i = c.heading('Write To Our Dental Office')
    contact['form'] = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))
    contact['map_address'] = [w for w in ws if w['widgetType'] == 'google_maps'][0]['settings']['address']
    C['contact'] = contact

    # ---------- FAQ page ----------
    f = Page(items['faqs']); ws = f.ws
    i = f.heading('Frequently Asked Questions?')
    qa = [[text(w['settings']['title_text']), text(w['settings']['description_text'])] for w in ws[i:] if w['widgetType'] == 'icon-box'][:8]
    i2 = f.heading('Our Accomplishments!')
    ctr = [w for w in ws[i2:] if w['widgetType'] == 'counter'][:4]
    j = f.heading('Trusted by Our Happy Patients')
    C['faq'] = dict(title=text(ws[i]['settings']['title']), image=248, items=qa,
                    stats=dict(eyebrow=text(ws[i2]['settings']['title']), title=' '.join(text(ws[i2 + 1]['settings']['title']).split()),
                               items=[[int(x['settings']['ending_number']), x['settings'].get('suffix', ''), text(x['settings']['title'])] for x in ctr]),
                    reviews=dict(eyebrow=text(ws[j]['settings']['title']), title=text(ws[j + 1]['settings']['title'])))

    # ---------- TESTIMONIAL ----------
    t = Page(items['testimonial']); ws = t.ws
    i = t.heading('Happy Customers!')
    C['testimonial'] = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))

    # ---------- SERVICES index ----------
    s = Page(items['our-services']); ws = s.ws
    i = s.heading('Our Services')
    C['services_page'] = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))
    i = s.heading('Happy Customers!')
    C['services_page']['reviews'] = dict(eyebrow=text(ws[i]['settings']['title']), title=text(ws[i + 1]['settings']['title']))

    # ---------- FORMS index ----------
    fo = Page(items['forms']); ws = fo.ws
    cards = []
    for n, w in enumerate(ws):
        if w['widgetType'] == 'image':
            cards.append(dict(image=img_id(w), title=text(ws[n + 1]['settings']['title']), slug=slug_of(ws[n + 2]['settings']['link']['url']),
                              button=text(ws[n + 2]['settings']['text'])))
    C['forms_page'] = dict(cards=cards)

    # ---------- SERVICE DETAIL PAGES ----------
    detail = {}
    for sv in services:
        p = items[sv['slug']]
        secs = [x for x in p['data'] if x['elType'] == 'section']
        hero_img = (secs[0]['settings'].get('background_overlay_image') or {}).get('id')
        w1, w2, w3 = flat([secs[1]]), flat([secs[2]]), flat([secs[3]])
        g = lambda ws_, ty: [w for w in ws_ if w['widgetType'] == ty]
        bx = lambda ws_: [[text(b['settings']['title_text']), text(b['settings']['description_text'])] for b in g(ws_, 'icon-box')]
        hd1, hd2, hd3 = g(w1, 'heading'), g(w2, 'heading'), g(w3, 'heading')
        detail[sv['slug']] = dict(
            title=p['title'], hero=int(hero_img) if hero_img else None,
            eyebrow=text(hd1[0]['settings']['title']), heading=text(hd1[1]['settings']['title']), lead=text(hd1[2]['settings']['title']),
            images=[img_id(w) for w in g(w1, 'image')], intro_boxes=bx(w1),
            expect_eyebrow=text(hd2[0]['settings']['title']), expect_heading=text(hd2[1]['settings']['title']), expect_boxes=bx(w2),
            care_image=img_id(g(w3, 'image')[0]), care_eyebrow=text(hd3[0]['settings']['title']), care_heading=text(hd3[1]['settings']['title']),
            care_text=paras(g(w3, 'text-editor')[0]['settings']['editor'])[0], care_boxes=bx(w3))
    C['service_pages'] = detail
    C['titles'] = {slug: p['title'] for slug, p in items.items()}
    return C


def php(v, ind=0):
    pad = '\t' * ind
    if isinstance(v, dict):
        if not v:
            return 'array()'
        return 'array(\n' + ''.join(f"{pad}\t{php(k)} => {php(x, ind + 1)},\n" for k, x in v.items()) + pad + ')'
    if isinstance(v, list):
        if not v:
            return 'array()'
        if all(not isinstance(x, (dict, list)) for x in v):
            return 'array( ' + ', '.join(php(x) for x in v) + ' )'
        return 'array(\n' + ''.join(f"{pad}\t{php(x, ind + 1)},\n" for x in v) + pad + ')'
    if v is None:
        return 'null'
    if isinstance(v, bool):
        return 'true' if v else 'false'
    if isinstance(v, int):
        return str(v)
    return "'" + str(v).replace('\\', '\\\\').replace("'", "\\'") + "'"


def source_strings(items):
    """Every visible text string on each old page, for the completeness check."""
    out = {}
    for slug, p in items.items():
        strs = []
        for w in flat(p['data']):
            st, ty = w['settings'], w['widgetType']
            for k in ('title', 'title_text', 'description_text', 'editor', 'text', 'address'):
                v = st.get(k)
                if isinstance(v, str) and v.strip():
                    strs.extend(paras(v) if ty == 'text-editor' else lines(re.sub(r'<br\s*/?>', ' ', v)) if k == 'description_text' else [text(v)])
            for k in ('icon_list', 'ekit_accordion_items', 'form_fields'):
                for r in st.get(k) or []:
                    for kk in ('text', 'acc_title', 'acc_content', 'field_label', 'placeholder', 'field_options'):
                        v = r.get(kk)
                        if isinstance(v, str) and v.strip():
                            strs.extend(text(x) for x in v.split('\n') if x.strip()) if kk == 'field_options' else strs.append(text(v))
        out[slug] = [s for s in dict.fromkeys(strs) if s]
    return out


if __name__ == '__main__':
    items = load(sys.argv[1])
    out = sys.argv[2]
    C = fix(build(items))
    with open(out + '/content.php', 'w') as f:
        f.write("<?php\n/**\n * Page copy carried over from the previous thedentalhauz.com site.\n * Generated by tools/build-content.py: edit the text here, or in the page templates.\n *\n * @package the-dental-hauz\n */\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\nreturn " + php(C) + ";\n")
    json.dump(source_strings(items), open(out + '/source-strings.json', 'w'), indent=1, ensure_ascii=False)
    print('pages:', len(items), '| services:', len(C['services']), '| service pages:', len(C['service_pages']))
