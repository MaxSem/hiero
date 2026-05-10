#!/usr/bin/python3
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

def cleanDir(dir):
    os.makedirs(dir, exist_ok=True)

    for file in glob.glob(dir + '/raw*.svg'):
        os.remove(file)

    if os.path.isfile(dir + '/_font.json'):
        os.remove(dir + '/_font.json')

def exportFont(fontPath, destDir):
    font = fontforge.open(fontPath)
    print(f'Exporting {font.familyname} {font.version}')

    count = 0
    for char in range(FIRST_CHAR, LAST_CHAR + 1):
        try:
            glyph = font[char]
        except TypeError:
            continue

        glyph.export(f'{destDir}/raw{char}.svg')
        count += 1

    if count == 0:
        print('No Egyptian hieroglyph glyphs found in the font!', file=sys.stderr)
        exit(1)

    info = {
        'name': font.familyname,
        'version': font.version,
        'copyright': font.copyright,
        'generator': 'FontForge ' + fontforge.version(),
    }

    f = open(destDir + '/_font.json', 'w')
    json.dump(info, f, indent=4)
    f.close()
    print(f'Exported {count} characters.')

if len(sys.argv) != 3:
        print(f'Syntax: {sys.argv[0]} <font_location> <output_dir>', file=sys.stderr)
        exit(1)

fontPath = sys.argv[1]
destDir = sys.argv[2]

cleanDir(destDir)
exportFont(fontPath, destDir)
