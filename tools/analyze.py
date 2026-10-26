import sys
from PIL import Image

path = sys.argv[1]
image = Image.open(path).convert('RGB')
crop = [int(v) for v in sys.argv[2:6]] if len(sys.argv) >= 6 else [0, 0, 0, 0]
if len(sys.argv) >= 6:
    image = image.crop((crop[0], crop[1], crop[2] or image.size[0], crop[3] or image.size[1]))
im = image
W, H = im.size
px = im.load()
print('crop', crop)

bg = tuple(int(v) for v in __import__('os').environ.get('BG', '').split(',')) if __import__('os').environ.get('BG') else px[3, H - 3]
print('size', im.size, 'bg', bg)


def is_content(p):
    return abs(p[0] - bg[0]) + abs(p[1] - bg[1]) + abs(p[2] - bg[2]) > 24


step = 2
colhas = [False] * W
rowhas = [False] * H
for y in range(0, H, step):
    for x in range(0, W, step):
        if is_content(px[x, y]):
            colhas[x] = True
            rowhas[y] = True

# fill small gaps
def bands(flags, gap):
    out = []
    start = None
    lastTrue = None
    for i, f in enumerate(flags):
        if f:
            if start is None:
                start = i
            lastTrue = i
        else:
            if start is not None and i - lastTrue > gap:
                out.append((start, lastTrue))
                start = None
    if start is not None:
        out.append((start, lastTrue))
    return out

cb = bands(colhas, 6)
rb = bands(rowhas, 8)
print('col bands', cb)
print('row bands', rb)

for (y0, y1) in rb:
    for (x0, x1) in cb:
        found = False
        for y in range(y0, y1 + 1, step):
            for x in range(x0, x1 + 1, step):
                if is_content(px[x, y]):
                    found = True
                    break
            if found:
                break
        if found:
            print(f'BOX x={x0}..{x1} (w={x1-x0+1})  y={y0}..{y1} (h={y1-y0+1})')
