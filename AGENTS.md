## ⚠ Design: read `docs/design_system/IGNITION-INDEX-DESIGN-SYSTEM.md` before any UI work

> This product was **CarTrak** and is now **Ignition Index**. It is mid-rebrand: the name, the
> visual system, and the design tokens have all been replaced.
>
> `docs/design_system/IGNITION-INDEX-DESIGN-SYSTEM.md` is the **binding** spec for anything with a
> visual surface — components, screens, emails, marketing pages. Read it in full before writing
> UI, and treat it as authoritative where it conflicts with existing code. Existing components
> that predate it are legacy, not precedent: do not copy their patterns forward.
>
> **Do not** reintroduce, extend, or pattern-match against the old system: the CarTrak name, the
> red accent, rounded corners, pill shapes, soft drop shadows, white floating cards, or filled
> icons. Encountering them in the codebase means that file has not been migrated yet.
>
> **Do** take every color, font, space and radius from the CSS variables in `styles.css`
> (`var(--color-*)`, `var(--font-*)`, `var(--space-*)`). Never hard-code a hex, a font name, or a
> px value the tokens already carry. The design doc lists the two roles the token sheet does not
> carry (shell navy, service-status amber/rust) and exactly where each is allowed.
>
> If a design decision is not covered by the doc, ask rather than inventing one.
