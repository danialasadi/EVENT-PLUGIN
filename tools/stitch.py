import os
import sys
import numpy as np
from PIL import Image

TILE_DIR = 'design/tiles'
ROWS = int(os.environ.get('ROWS', '8'))
COLS = int(os.environ.get('COLS', '3'))

OX0 = int(os.environ.get('OX0', '1457'))   # design x=0 (Events Desktop frame left edge)
OY0 = int(os.environ.get('OY0', '1097'))   # design y=0 (frame top edge)
STEP_X = 3100
STEP_Y = 1560
BX0, BX1 = 0, 3175      # band x in image px (right edge = browser scrollbar)
BY0, BY1 = 140, 1760    # band y in image px (below top bar, above banner)

X0, Y0, X1, Y1 = [int(v) for v in os.environ.get('BBOX', '-1500,-300,7950,11600').split(',')]


def load(j, k):
    p = f'{TILE_DIR}/t{j}_{k}.png'
    if not os.path.exists(p):
        return None
    return Image.open(p).convert('RGB')


# --- overlap verification -------------------------------------------------
def verify():
    for j in range(ROWS):
        for k in range(COLS - 1):
            a, b = load(j, k), load(j, k + 1)
            if a is None or b is None:
                continue
            ax0 = BX0 - OX0 + STEP_X * k
            ax1 = BX1 - OX0 + STEP_X * k
            bx0 = BX0 - OX0 + STEP_X * (k + 1)
            bx1 = BX1 - OX0 + STEP_X * (k + 1)
            lo, hi = max(ax0, bx0), min(ax1, bx1)
            ya0, ya1 = BY0, min(BY1, BY0 + 900)
            sa = np.asarray(a.crop((lo - ax0, ya0, hi - ax0, ya1)), dtype=np.int16)
            sb = np.asarray(b.crop((lo - bx0, ya0, hi - bx0, ya1)), dtype=np.int16)
            d = np.abs(sa - sb).mean()
            print(f'overlap row{j} col{k}->{k+1} width={hi-lo} meanabsdiff={d:.2f}')
    for j in range(ROWS - 1):
        a, b = load(j, 0), load(j + 1, 0)
        if a is None or b is None:
            continue
        ay0 = BY0 - OY0 + STEP_Y * j
        ay1 = BY1 - OY0 + STEP_Y * j
        by0 = BY0 - OY0 + STEP_Y * (j + 1)
        by1 = BY1 - OY0 + STEP_Y * (j + 1)
        lo, hi = max(ay0, by0), min(ay1, by1)
        xa0, xa1 = BX0 + 200, BX0 + 1200
        sa = np.asarray(a.crop((xa0, lo - ay0 + BY0, xa1, hi - ay0 + BY0)), dtype=np.int16)
        sb = np.asarray(b.crop((xa0, lo - by0 + BY0, xa1, hi - by0 + BY0)), dtype=np.int16)
        print(f'overlap v row{j}->{j+1} height={hi-lo} meanabsdiff={np.abs(sa-sb).mean():.2f}')


def stitch(canvas):
    for j in range(ROWS):
        for k in range(COLS):
            im = load(j, k)
            if im is None:
                print('MISSING', j, k)
                continue
            tile = im.crop((BX0, BY0, BX1, BY1))
            dx = (BX0 - OX0 + STEP_X * k) - X0
            dy = (BY0 - OY0 + STEP_Y * j) - Y0
            canvas.paste(tile, (dx, dy))


if __name__ == '__main__':
    mode = sys.argv[1] if len(sys.argv) > 1 else 'stitch'
    if mode == 'verify':
        verify()
        sys.exit()
    canvas = Image.new('RGB', (X1 - X0, Y1 - Y0), (244, 245, 251))
    stitch(canvas)
    print('stitched', canvas.size)
    out = sys.argv[2] if len(sys.argv) > 2 else 'design/stitched.png'
    canvas.save(out)
    print('saved', out)
