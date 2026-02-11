export class GisLayersControl {
  constructor(manager, { title = "Layers" } = {}) {
    this.manager = manager
    this.title = title
    this._container = null
  }

  onAdd(map) {
    this.map = map
    const el = document.createElement("div")
    el.className = "maplibregl-ctrl maplibregl-ctrl-group"
    el.style.padding = "10px"
    el.style.minWidth = "220px"
    el.style.maxHeight = "320px"
    el.style.overflow = "auto"

    el.innerHTML = `
      <div style="font-weight:600; font-size:13px; margin-bottom:8px;">${this.title}</div>
      <div data-gis-layers-list></div>
    `

    this._container = el
    this.render()

    return el
  }

  onRemove() {
    this._container?.parentNode?.removeChild(this._container)
    this.map = undefined
  }

  render() {
    if (!this._container) return
    const host = this._container.querySelector("[data-gis-layers-list]")
    if (!host) return

    const layers = this.manager.getLayersForUI()

    // group by category
    const grouped = layers.reduce((acc, l) => {
      const cat = l.category || "Other"
      acc[cat] ||= []
      acc[cat].push(l)
      return acc
    }, {})

    host.innerHTML = Object.entries(grouped)
      .map(([cat, items]) => {
        const rows = items.map(l => {
          const checked = l.visible ? "checked" : ""
          return `
            <label style="display:flex; align-items:center; gap:8px; margin:6px 0; cursor:pointer;">
              <input type="checkbox" data-layer-key="${l.key}" ${checked} />
              <span style="display:inline-block; width:10px; height:10px; border-radius:3px; background:${l.color};"></span>
              <span style="font-size:12px;">${l.display_name}</span>
            </label>
          `
        }).join("")

        return `
          <div style="margin-top:8px;">
            <div style="font-size:12px; font-weight:600; opacity:0.9; margin-bottom:4px;">${cat}</div>
            ${rows}
          </div>
        `
      })
      .join("")

    host.querySelectorAll("input[type=checkbox][data-layer-key]").forEach(cb => {
      cb.addEventListener("change", (e) => {
        const key = e.target.getAttribute("data-layer-key")
        this.manager.setVisible(key, e.target.checked)
      })
    })
  }
}
