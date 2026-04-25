import pypdf
import os
import glob

docs_dir = r"c:\Users\santi\Proyectos\IPS\AvanzarIPS\Documentos"
files = glob.glob(os.path.join(docs_dir, '*.pdf'))

for file in files:
    try:
        reader = pypdf.PdfReader(file)
        text = ''
        for page in reader.pages:
            text += page.extract_text() + "\n"
        
        out_name = file.replace('.pdf', '.txt')
        with open(out_name, 'w', encoding='utf-8') as f:
            f.write(text)
        print(f"Extracted {os.path.basename(file)} to {os.path.basename(out_name)}")
    except Exception as e:
        print(f"Error parsing {os.path.basename(file)}: {e}")
