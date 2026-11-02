"""Manim scene for PRBA lecture 6: sequential scan vs. B-tree index scan.

The same query runs side by side on the same `orders` table:
left without an index (Seq Scan reads every row), right with a B-tree index
on `customer` (Index Scan walks root -> internal node -> leaf -> one row).

Render from the repository root (see _manim/README.md):
    .venv/bin/manim -qm _manim/seq_vs_index.py SeqScanVsIndexScan
"""

from manim import *

MONO = "Menlo"
SANS = "Helvetica Neue"
TARGET = "Nowak"

# Heap order of `orders`: rows are stored in insertion order, so names are unsorted.
HEAP = [
    (1, "Kot"), (2, "Sowa"), (3, "Bąk"), (4, "Lis"),
    (5, "Zając"), (6, "Dudek"), (7, "Maj"), (8, "Rak"),
    (9, "Gil"), (10, "Pawlak"), (11, "Nowak"), (12, "Cios"),
    (13, "Wilk"), (14, "Hołub"), (15, "Fryc"), (16, "Kania"),
]
ID_OF = {name: row_id for row_id, name in HEAP}

# B-tree on `customer`: sorted keys in leaves, each key points to its row id.
LEAVES = [
    ["Bąk", "Cios", "Dudek", "Fryc"],
    ["Gil", "Hołub", "Kania", "Kot"],
    ["Lis", "Maj", "Nowak", "Pawlak"],
    ["Rak", "Sowa", "Wilk", "Zając"],
]
ROOT_KEY = "Lis"              # < Lis -> left subtree, >= Lis -> right subtree
INTERNAL_KEYS = ["Gil", "Rak"]

# Layout (frame is ~14.2 x 8 units).
ROW_H = 0.3
ID_W, NAME_W = 0.6, 1.6
TABLE_TOP = 2.45
LEFT_TABLE_X = -5.2
RIGHT_TABLE_X = 5.7
DIVIDER_X = -3.75
LEAF_W, LEAF_GAP, LEAF_Y = 1.45, 0.2, -1.6
TREE_X = 0.45
INTERNAL_Y, ROOT_Y = 0.35, 1.85

SCANNED = GREY_C
HIT = GREEN_C
ACTIVE = YELLOW


def make_table(x: float) -> VGroup:
    """Build the `orders` table; returns VGroup(header, row_0, ..., row_15)."""
    def row(id_txt: str, name_txt: str, weight: str = NORMAL) -> VGroup:
        box = Rectangle(width=ID_W + NAME_W, height=ROW_H, stroke_width=1, stroke_color=GREY_B)
        sep = Line(box.get_top(), box.get_bottom(), stroke_width=1, stroke_color=GREY_B)
        sep.shift(LEFT * (box.width / 2 - ID_W))
        id_t = Text(id_txt, font=MONO, font_size=15, weight=weight)
        name_t = Text(name_txt, font=MONO, font_size=15, weight=weight)
        id_t.move_to(box.get_left() + RIGHT * ID_W / 2)
        name_t.move_to(box.get_left() + RIGHT * (ID_W + NAME_W / 2))
        return VGroup(box, sep, id_t, name_t)

    header = row("id", "customer", weight=BOLD)
    header[0].set_fill(GREY_E, opacity=1)
    rows = [row(str(i), name) for i, name in HEAP]
    table = VGroup(header, *rows).arrange(DOWN, buff=0)
    table.move_to([x, TABLE_TOP - table.height / 2, 0])
    return table


def make_node(lines: list[str], width: float, center) -> VGroup:
    """B-tree node: rounded box with stacked text entries; returns VGroup(box, entries)."""
    entries = VGroup(*[Text(s, font=MONO, font_size=15) for s in lines])
    entries.arrange(DOWN, buff=0.1, aligned_edge=LEFT)
    if entries.width > width - 0.2:
        entries.scale_to_fit_width(width - 0.2)
    box = RoundedRectangle(
        corner_radius=0.08, width=width, height=entries.height + 0.25,
        stroke_color=BLUE_B, stroke_width=2,
    )
    box.set_fill(BLUE_E, opacity=0.35)
    node = VGroup(box, entries)
    node.move_to(center)
    entries.move_to(box)
    return node


def counter(n: int, anchor) -> VGroup:
    """Read counter: VGroup(label, number); the number is swapped in place each tick."""
    label = Text("odczyty:", font=MONO, font_size=20)
    count = VGroup(label, Text(str(n), font=MONO, font_size=20)).move_to(anchor)
    set_count(count, n)
    return count


def set_count(count: VGroup, n: int) -> None:
    num = Text(str(n), font=MONO, font_size=20).next_to(count[0], RIGHT, buff=0.15)
    # Align to the baseline of "o" (not the box bottom, which includes the "y" descender).
    count[1].become(num.align_to(count[0][0], DOWN))


class SeqScanVsIndexScan(Scene):
    def construct(self):
        # --- 1. Setup: query, two identical tables ------------------------------
        query = Text(
            "SELECT * FROM orders WHERE customer = 'Nowak';",
            font=MONO, font_size=24, t2c={"'Nowak'": ACTIVE},
        ).to_edge(UP, buff=0.25)

        left_table = make_table(LEFT_TABLE_X)
        right_table = make_table(RIGHT_TABLE_X)
        divider = DashedLine([DIVIDER_X, 3.0, 0], [DIVIDER_X, -3.8, 0], stroke_color=GREY_D)

        left_label = Text("bez indeksu", font=SANS, font_size=24, color=RED_C)
        left_label.move_to([LEFT_TABLE_X, 2.8, 0])
        right_label = Text("z indeksem B-tree", font=SANS, font_size=24, color=HIT)
        right_label.move_to([(DIVIDER_X + 7.1) / 2, 2.8, 0])

        self.play(Write(query), run_time=1.5)
        self.play(
            FadeIn(left_table, shift=0.2 * UP), FadeIn(right_table, shift=0.2 * UP),
            Create(divider), FadeIn(left_label), FadeIn(right_label),
            run_time=1.5,
        )
        self.wait(1.5)

        # --- 2. Build the index on the right ------------------------------------
        create_idx = Text(
            "CREATE INDEX idx_customer ON orders(customer);", font=MONO, font_size=18, color=HIT,
        ).move_to([(DIVIDER_X + 7.1) / 2, -3.55, 0])

        leaf_xs = [
            TREE_X + (k - 1.5) * (LEAF_W + LEAF_GAP) for k in range(len(LEAVES))
        ]
        leaves = [
            make_node([f"{name} → {ID_OF[name]}" for name in keys], LEAF_W, [x, LEAF_Y, 0])
            for keys, x in zip(LEAVES, leaf_xs)
        ]
        internals = [
            make_node([key], 0.9, [(leaf_xs[2 * k] + leaf_xs[2 * k + 1]) / 2, INTERNAL_Y, 0])
            for k, key in enumerate(INTERNAL_KEYS)
        ]
        root = make_node([ROOT_KEY], 0.9, [TREE_X, ROOT_Y, 0])

        def edge(parent: VGroup, child: VGroup) -> Line:
            return Line(parent.get_bottom(), child.get_top(), stroke_width=2, stroke_color=BLUE_B)

        root_edges = [edge(root, n) for n in internals]
        leaf_edges = [edge(internals[k // 2], leaves[k]) for k in range(len(leaves))]

        self.play(Write(create_idx), run_time=1.5)
        # Leaves first: the index is a sorted copy of the key column plus row pointers.
        self.play(LaggedStart(*[FadeIn(l, shift=0.2 * UP) for l in leaves], lag_ratio=0.25), run_time=2)
        self.play(
            LaggedStart(*[FadeIn(n) for n in internals], lag_ratio=0.3),
            LaggedStart(*[Create(e) for e in leaf_edges], lag_ratio=0.2),
            run_time=1.5,
        )
        self.play(FadeIn(root), *[Create(e) for e in root_edges], run_time=1)
        tree_note = Text(
            "liście: klucze posortowane + numer wiersza", font=SANS, font_size=16, color=GREY_B,
        ).next_to(VGroup(*leaves), DOWN, buff=0.15)
        self.play(FadeIn(tree_note))
        self.wait(1.5)
        self.play(FadeOut(create_idx), FadeOut(tree_note))

        # --- 3. Race: both plans step at the same pace ---------------------------
        left_count = counter(0, [LEFT_TABLE_X, -3.0, 0])
        right_count = counter(0, [RIGHT_TABLE_X, -3.0, 0])
        self.play(FadeIn(left_count), FadeIn(right_count))

        left_rows = left_table[1:]
        right_rows = right_table[1:]
        cursor = SurroundingRectangle(left_rows[0], color=ACTIVE, buff=0.02, stroke_width=3)

        target_leaf = 2
        target_entry = leaves[target_leaf][1][LEAVES[target_leaf].index(TARGET)]
        target_row = right_rows[ID_OF[TARGET] - 1]
        jump = CurvedArrow(
            target_entry.get_right() + 0.08 * RIGHT, target_row.get_left(),
            angle=-0.7 * PI, color=HIT, stroke_width=3, tip_length=0.18,
        )

        # Key comparisons are shown in a fixed caption slot under the tree.
        def comparison(text: str) -> Text:
            return Text(text, font=MONO, font_size=18, color=ACTIVE).move_to([TREE_X, -2.75, 0])

        cmp_root = comparison("Nowak ≥ Lis → w prawo")
        cmp_internal = comparison("Nowak < Rak → w lewo")
        cmp_leaf = comparison("Nowak → wiersz nr 11")

        # Right-side steps, one per tick: root, internal node, leaf, row fetch.
        right_steps = [
            lambda: [root[0].animate.set_stroke(ACTIVE, width=4), FadeIn(cmp_root)],
            lambda: [
                root_edges[1].animate.set_stroke(ACTIVE, width=4),
                internals[1][0].animate.set_stroke(ACTIVE, width=4),
                FadeOut(cmp_root), FadeIn(cmp_internal),
            ],
            lambda: [
                leaf_edges[target_leaf].animate.set_stroke(ACTIVE, width=4),
                leaves[target_leaf][0].animate.set_stroke(ACTIVE, width=4),
                target_entry.animate.set_color(HIT),
                FadeOut(cmp_internal), FadeIn(cmp_leaf),
            ],
            lambda: [
                Create(jump),
                target_row[0].animate.set_fill(HIT, opacity=0.45),
                FadeOut(cmp_leaf),
            ],
        ]
        right_done = Text("gotowe ✓", font=SANS, font_size=22, color=HIT).next_to(right_count, DOWN, buff=0.12)
        left_found = Text(
            "jest Nowak…\nale czy nie ma kolejnych?", font=SANS, font_size=16, line_spacing=0.8, color=ACTIVE,
        ).next_to(left_count, DOWN, buff=0.12)

        self.play(Create(cursor), run_time=0.4)
        for i, (_, name) in enumerate(HEAP):
            anims = [cursor.animate.move_to(left_rows[i])]
            is_hit = name == TARGET
            if is_hit:
                anims.append(left_rows[i][0].animate.set_fill(HIT, opacity=0.45))
            else:
                anims.append(left_rows[i][2:].animate.set_color(SCANNED))
            set_count(left_count, i + 1)
            if i < len(right_steps):
                anims += right_steps[i]()
                set_count(right_count, i + 1)
            if i == len(right_steps):
                anims.append(FadeIn(right_done, scale=1.2))
            if is_hit:
                anims.append(FadeIn(left_found))
            self.play(*anims, run_time=0.9)
            # The engine cannot stop at the first match: without an index it
            # has no guarantee that no other 'Nowak' follows.
            if is_hit:
                self.wait(0.8)
        self.play(FadeOut(cursor), FadeOut(left_found))
        self.wait(2)

        # --- 4. Scale: 16 rows vs 10 million rows -------------------------------
        self.play(*[FadeOut(m) for m in self.mobjects if m is not query])

        def cell(s: str, color=WHITE, weight=NORMAL) -> Text:
            return Text(s, font=SANS, font_size=26, color=color, weight=weight)

        grid = VGroup(
            cell(""), cell("16 wierszy", weight=BOLD), cell("10 mln wierszy", weight=BOLD),
            cell("bez indeksu", RED_C), cell("16", RED_C), cell("10 000 000", RED_C),
            cell("z indeksem", HIT), cell("4", HIT), cell("≈ 4–5", HIT),
        ).arrange_in_grid(rows=3, cols=3, buff=(0.9, 0.45), col_alignments="lrr")
        grid.move_to(0.9 * UP)
        grid_title = Text("Liczba odczytów", font=SANS, font_size=28).next_to(grid, UP, buff=0.5)
        footnote = Text(
            "* węzeł B-drzewa w prawdziwej bazie mieści setki kluczy,\n"
            "  więc 10 mln wierszy to tylko 3–4 poziomy drzewa",
            font=SANS, font_size=18, color=GREY_B, line_spacing=0.8,
        ).next_to(grid, DOWN, buff=0.5)

        self.play(FadeIn(grid_title), LaggedStart(*[FadeIn(c) for c in grid], lag_ratio=0.1), run_time=2)
        self.play(FadeIn(footnote))
        self.wait(3)

        # --- 5. Link to EXPLAIN output -------------------------------------------
        self.play(FadeOut(grid), FadeOut(grid_title), FadeOut(footnote))
        explain_title = Text("Co pokaże EXPLAIN?", font=SANS, font_size=28).next_to(query, DOWN, buff=0.6)
        seq_plan = VGroup(
            Text("bez indeksu:", font=SANS, font_size=22, color=RED_C),
            Text("Seq Scan on orders\n  Filter: (customer = 'Nowak')", font=MONO, font_size=20),
        ).arrange(DOWN, aligned_edge=LEFT, buff=0.15)
        idx_plan = VGroup(
            Text("z indeksem:", font=SANS, font_size=22, color=HIT),
            Text(
                "Index Scan using idx_customer on orders\n  Index Cond: (customer = 'Nowak')",
                font=MONO, font_size=20,
            ),
        ).arrange(DOWN, aligned_edge=LEFT, buff=0.15)
        plans = VGroup(seq_plan, idx_plan).arrange(DOWN, aligned_edge=LEFT, buff=0.6)
        plans.next_to(explain_title, DOWN, buff=0.6)

        self.play(FadeIn(explain_title))
        self.play(FadeIn(seq_plan, shift=0.2 * RIGHT))
        self.play(FadeIn(idx_plan, shift=0.2 * RIGHT))
        self.wait(4)
