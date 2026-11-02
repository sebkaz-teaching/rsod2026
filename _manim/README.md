# Shared index animation

`seq_vs_index.py` is the unchanged source of the PRBA lecture 6 animation,
reused by the RSOD supplement `lectures/indeksy.qmd`.
The committed output is `lectures/media/seq_vs_index.mp4`.

The animation illustrates the access paths using an orders/customer example.
The accompanying SQL demonstration uses the course enrollment model; it is
not a recording of PostgreSQL executing that demonstration.

To rebuild with an existing Manim environment, run from this repository:

```bash
python -m manim -qm --media_dir /tmp/rsod-index-animation _manim/seq_vs_index.py SeqScanVsIndexScan
cp /tmp/rsod-index-animation/videos/seq_vs_index/720p30/SeqScanVsIndexScan.mp4 lectures/media/seq_vs_index.mp4
```

See the PRBA `_manim/README.md` for the original environment instructions.
