import zipfile
import xml.etree.ElementTree as ET

with zipfile.ZipFile('ibbunvAkademy.xlsm.xls', 'r') as z:
    # Read shared strings
    ss_xml = z.read('xl/sharedStrings.xml')
    # parse ignoring namespace
    root = ET.fromstring(ss_xml)
    shared_strings = []
    for elem in root:
        text = "".join(elem.itertext())
        shared_strings.append(text)
    print(f"Total shared strings: {len(shared_strings)}")

    # Let's see some strings
    print("Sample shared strings:", shared_strings[50:100])

    # Workbook rels
    rels_xml = z.read('xl/_rels/workbook.xml.rels')
    rels_root = ET.fromstring(rels_xml)
    sheet_targets = {}
    for rel in rels_root:
        if 'Relationship' in rel.tag:
            sheet_targets[rel.attrib.get('Id')] = rel.attrib.get('Target')
    
    wb_root = ET.fromstring(z.read('xl/workbook.xml'))
    sheet_files = {}
    for sheet in wb_root.iter():
        if sheet.tag.endswith('sheet') and 'name' in sheet.attrib:
            rId = sheet.attrib.get('{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id')
            sheet_files[sheet.attrib['name']] = 'xl/' + sheet_targets.get(rId, '')
    print("Sheet files:", sheet_files)

    def read_sheet(s_name):
        fpath = sheet_files.get(s_name)
        if not fpath or fpath not in z.namelist():
            return {}
        s_root = ET.fromstring(z.read(fpath))
        data = {}
        for row in s_root.iter():
            if row.tag.endswith('row'):
                r_num = int(row.attrib['r'])
                cols = {}
                for c in row.iter():
                    if c.tag.endswith('c'):
                        ref = c.attrib['r']
                        col_name = ''.join(filter(str.isalpha, ref))
                        t = c.attrib.get('t')
                        v_el = None
                        for child in c:
                            if child.tag.endswith('v'):
                                v_el = child
                                break
                        val = ""
                        if v_el is not None and v_el.text:
                            if t == 's':
                                val = shared_strings[int(v_el.text)]
                            else:
                                val = v_el.text
                        cols[col_name] = val
                data[r_num] = cols
        return data

    for target_sheet in ['seting1', 'sheet2', 'star1', 'prnn']:
        res = read_sheet(target_sheet)
        print(f"\n================ SHEET: {target_sheet} (Rows: {len(res)}) ================")
        for r_num in sorted(res.keys())[:25]:
            vals = {k: v for k, v in res[r_num].items() if v}
            if vals:
                print(f"Row {r_num}: {vals}")

