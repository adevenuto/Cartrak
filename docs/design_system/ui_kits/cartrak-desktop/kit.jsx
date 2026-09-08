/* Shared helper for the desktop kit — Icon (Lucide). Exported to window. */
function Icon({ n, s = 20, color, strokeWidth = 2, style = {} }) {
  const ref = React.useRef();
  React.useEffect(() => {
    if (ref.current && window.lucide && lucide[n]) {
      ref.current.innerHTML = '';
      const el = lucide.createElement(lucide[n]);
      el.setAttribute('width', s); el.setAttribute('height', s);
      el.setAttribute('stroke-width', strokeWidth);
      ref.current.appendChild(el);
    }
  }, [n, s, strokeWidth]);
  return <span ref={ref} style={{ display: 'inline-flex', color, ...style }} />;
}
window.Icon = Icon;
