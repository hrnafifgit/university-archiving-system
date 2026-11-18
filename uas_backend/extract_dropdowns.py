import zipfile
import xml.etree.ElementTree as ET

with zipfile.ZipFile('ibbunvAkademy.xlsm.xls', 'r') as z:
    # Read shared strings
    ss_xml = z.read('xl/sharedStrings.xml')
    root = ET.fromstring(ss_xml)
    shared_strings = []
    for elem in root:
        text = "".join(elem.itertext()).strip()
        shared_strings.append(text)

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

    def get_column_values(s_name):
        fpath = sheet_files.get(s_name)
        if not fpath:
            return {}
        s_root = ET.fromstring(z.read(fpath))
        cols_values = {}
        for row in s_root.iter():
            if row.tag.endswith('row'):
                for c in row.iter():
                    if c.tag.endswith('c'):
                        ref = c.attrib.get('r', '')
                        col_name = ''.join(filter(str.isalpha, ref))
                        if not col_name:
                            continue
                        t = c.attrib.get('t')
                        v_el = None
                        for child in c:
                            if child.tag.endswith('v'):
                                v_el = child
                                break
                        val = ""
                        if v_el is not None and v_el.text:
                            if t == 's':
                                idx = int(v_el.text)
                                if idx < len(shared_strings):
                                    val = shared_strings[idx]
                            else:
                                val = v_el.text.strip()
                        if val:
                            if col_name not in cols_values:
                                cols_values[col_name] = []
                            cols_values[col_name].append(val)
        return cols_values

    # Extract seting1
    seting_cols = get_column_values('seting1')
    print("SETING1 Columns:")
    extracted_dropdowns = {}
    for col, vals in sorted(seting_cols.items()):
        header = vals[0] if len(vals) > 0 else col
        # filter out header and numbers
        unique_vals = []
        for v in vals:
            if v != header and v not in unique_vals and not v.isdigit():
                unique_vals.append(v)
        extracted_dropdowns[header] = unique_vals
        print(f"Col {col} ({header}): {len(unique_vals)} unique values -> {unique_vals[:10]}")

    # Also extract Feuil1 unique values
    feuil_cols = get_column_values('Feuil1')
    print("\nFEUIL1 Columns:")
    for col, vals in sorted(feuil_cols.items()):
        header = vals[0] if len(vals) > 0 else col
        unique_vals = []
        for v in vals:
            if v != header and v not in unique_vals and not v.isdigit():
                unique_vals.append(v)
        print(f"Feuil1 Col {col} ({header}): {len(unique_vals)} unique values -> {unique_vals[:8]}")

