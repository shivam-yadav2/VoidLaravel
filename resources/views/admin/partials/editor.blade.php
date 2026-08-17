{{--
  Lightweight HTML editor: a plain textarea plus a toolbar that wraps the
  selection in a tag. Deliberately dependency-free — no CDN, no build step — so
  the admin keeps working offline and can never be broken by a third-party CDN.

  Content is stored and rendered as raw HTML, which is safe here because only
  authenticated admins can write it.

  Expects: $name, $value, optional $rows
--}}
<div class="adm-editor" data-editor>
  <div class="adm-editor__bar">
    <button type="button" data-wrap="h2">H2</button>
    <button type="button" data-wrap="h3">H3</button>
    <button type="button" data-wrap="p">P</button>
    <button type="button" data-wrap="strong"><strong>B</strong></button>
    <button type="button" data-wrap="em"><em>I</em></button>
    <button type="button" data-wrap="ul">List</button>
    <button type="button" data-wrap="blockquote">Quote</button>
    <button type="button" data-wrap="a">Link</button>
    <button type="button" data-wrap="code">Code</button>
    <span class="adm-editor__hint">HTML supported</span>
  </div>

  <textarea
    id="{{ $name }}"
    name="{{ $name }}"
    rows="{{ $rows ?? 20 }}"
    class="adm-editor__area"
    data-editor-area
    spellcheck="true"
  >{{ $value }}</textarea>
</div>

@once
  @push('scripts')
    <script>
      document.querySelectorAll("[data-editor]").forEach(function (editor) {
        var area = editor.querySelector("[data-editor-area]");

        editor.querySelectorAll("[data-wrap]").forEach(function (btn) {
          btn.addEventListener("click", function () {
            var tag = btn.getAttribute("data-wrap");
            var start = area.selectionStart;
            var end = area.selectionEnd;
            var selected = area.value.slice(start, end);
            var replacement;

            if (tag === "ul") {
              var lines = (selected || "Item").split("\n").filter(function (l) { return l.trim(); });
              replacement = "<ul>\n" + lines.map(function (l) {
                return "  <li>" + l.trim() + "</li>";
              }).join("\n") + "\n</ul>";
            } else if (tag === "a") {
              var url = prompt("Link URL:", "https://");
              if (!url) return;
              replacement = '<a href="' + url + '">' + (selected || url) + "</a>";
            } else {
              replacement = "<" + tag + ">" + (selected || "") + "</" + tag + ">";
            }

            area.setRangeText(replacement, start, end, "end");
            area.focus();
          });
        });
      });
    </script>
  @endpush
@endonce
