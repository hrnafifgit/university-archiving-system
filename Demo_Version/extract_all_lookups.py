import zipfile, json
import xml.etree.ElementTree as ET

with zipfile.ZipFile('ibbunvAkademy.xlsm.xls', 'r') as z:
    ss_xml = z.read('xl/sharedStrings.xml')
    root = ET.fromstring(ss_xml)
    shared_strings = ["".join(elem.itertext()).strip() for elem in root]

    rels_xml = z.read('xl/_rels/workbook.xml.rels')
    rels_root = ET.fromstring(rels_xml)
    sheet_targets = {rel.attrib.get('Id'): rel.attrib.get('Target') for rel in rels_root if 'Relationship' in rel.tag}
    
    wb_root = ET.fromstring(z.read('xl/workbook.xml'))
    sheet_files = {}
    for sheet in wb_root.iter():
        if sheet.tag.endswith('sheet') and 'name' in sheet.attrib:
            rId = sheet.attrib.get('{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id')
            sheet_files[sheet.attrib['name']] = 'xl/' + sheet_targets.get(rId, '')

    def get_cols(s_name):
        fpath = sheet_files.get(s_name)
        if not fpath: return {}
        s_root = ET.fromstring(z.read(fpath))
        cols = {}
        for row in s_root.iter():
            if row.tag.endswith('row'):
                for c in row.iter():
                    if c.tag.endswith('c'):
                        ref = c.attrib.get('r', '')
                        col_name = ''.join(filter(str.isalpha, ref))
                        if not col_name: continue
                        t = c.attrib.get('t')
                        v_el = next((child for child in c if child.tag.endswith('v')), None)
                        val = ""
                        if v_el is not None and v_el.text:
                            if t == 's':
                                idx = int(v_el.text)
                                if idx < len(shared_strings): val = shared_strings[idx]
                            else:
                                val = v_el.text.strip()
                        if val:
                            cols.setdefault(col_name, []).append(val)
        return cols

    seting = get_cols('seting1')
    feuil = get_cols('Feuil1')

    def clean_unique(lst, skip_header=True):
        res = []
        start = 1 if skip_header and lst else 0
        for item in lst[start:]:
            item = item.strip()
            if item and item not in res and not item.isdigit() and item != 'غير محدد':
                res.append(item)
        return res

    data = {
        'entities': clean_unique(seting.get('D', [])),
        'departments': clean_unique(seting.get('E', [])),
        'ranks': clean_unique(seting.get('F', [])),
        'job_statuses': clean_unique(seting.get('G', [])),
        'employment_statuses': clean_unique(seting.get('B', [])),
        'degrees': clean_unique(seting.get('M', [])),
        'specializations': clean_unique(seting.get('L', []))[:50],
        'admin_jobs': clean_unique(seting.get('H', [])),
        'universities': clean_unique(feuil.get('W', []) + ['جامعة إب', 'جامعة صنعاء', 'جامعة القاهرة', 'جامعة أسيوط']),
        'countries': clean_unique(feuil.get('X', []) + ['اليمن', 'مصر', 'الهند', 'السعودية', 'العراق', 'الأردن', 'سوريا', 'ماليزيا'])
    }

    with open('uas_backend/extracted_excel_lookups.json', 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

    print("Extracted Data Summary:")
    for k, v in data.items():
        print(f" - {k}: {len(v)} items -> {v[:5]}")
