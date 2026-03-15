import sys
import os
import glob
import json

try:
    import fontforge
except ModuleNotFoundError:
    print('The FontForge Python package is missing, try apt install python3-fontforge or something...', file=sys.stderr)
    exit(1)

FIRST_CHAR = 0x13000
LAST_CHAR = 0x1342F

fontPath = 'data/tmp/noto.ttf'
destDir = 'data/tmp'

def cleanDir(dir):
    os.makedirs(dir, exist_ok=True)

    for file in glob.glob(dir + '/raw*.svg'):
        os.remove(file)

    if os.path.isfile(dir + '/font.json'):
        os.remove(dir + '/font.json')

def exportFont(fontPath, destDir):
    font = fontforge.open(fontPath)
    print(font.familyname, font.version)

    count = 0
    for char in range(FIRST_CHAR, LAST_CHAR + 1):
        try:
            glyph = font[char]
        except TypeError:
            continue

        glyph.export(f'{destDir}/raw{char}.svg')
        count += 1

    if count == 0:
        print('No hieroglyphic glyphs found in the font!', file=sys.stderr)
        exit(1)

    info = {
        'name': font.familyname,
        'version': font.version,
        'copyright': font.copyright,
        'generator': 'FontForge ' + fontforge.version(),
    }

    f = open(destDir + '/font.json', 'w')
    json.dump(info, f, indent=4)
    f.close()

cleanDir(destDir)
exportFont(fontPath, destDir)
