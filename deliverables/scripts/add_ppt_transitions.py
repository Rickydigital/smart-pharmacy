from __future__ import annotations

import re
import sys
import tempfile
import zipfile
from pathlib import Path


TRANSITION_XML = '<p:transition spd="med" advClick="1"><p:fade/></p:transition>'
TRANSITION_RE = re.compile(
    r"<p:transition\b[^>]*/>|<p:transition\b[^>]*>.*?</p:transition>",
    re.DOTALL,
)


def add_transition(slide_xml: str) -> str:
    slide_xml = TRANSITION_RE.sub("", slide_xml)
    if "</p:clrMapOvr>" in slide_xml:
        return slide_xml.replace("</p:clrMapOvr>", f"</p:clrMapOvr>{TRANSITION_XML}", 1)
    if "</p:cSld>" in slide_xml:
        return slide_xml.replace("</p:cSld>", f"</p:cSld>{TRANSITION_XML}", 1)
    raise ValueError("Slide XML does not contain a valid insertion point")


def main() -> int:
    if len(sys.argv) != 2:
        print("Usage: add_ppt_transitions.py <presentation.pptx>")
        return 2

    pptx_path = Path(sys.argv[1]).resolve()
    if not pptx_path.exists():
        raise FileNotFoundError(pptx_path)

    with tempfile.NamedTemporaryFile(delete=False, suffix=".pptx") as tmp:
        tmp_path = Path(tmp.name)

    changed = 0
    with zipfile.ZipFile(pptx_path, "r") as zin, zipfile.ZipFile(tmp_path, "w", zipfile.ZIP_DEFLATED) as zout:
        for item in zin.infolist():
            data = zin.read(item.filename)
            if re.fullmatch(r"ppt/slides/slide\d+\.xml", item.filename):
                xml = data.decode("utf-8")
                data = add_transition(xml).encode("utf-8")
                changed += 1
            zout.writestr(item, data)

    tmp_path.replace(pptx_path)
    print(f"added fade transitions to {changed} slides: {pptx_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
