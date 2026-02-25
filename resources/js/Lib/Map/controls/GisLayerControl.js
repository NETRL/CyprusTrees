export class GisLayersControl {
  constructor(manager, { title = "Map Layers" } = {}) {
    this.manager = manager;
    this.title = title;
    
    // UI Elements
    this._container = null;
    this._toggleBtn = null;
    this._panel = null;
    this._layerList = null;

    // State
    this._isExpanded = false;
    this._clickOutsideHandler = this._handleClickOutside.bind(this);
  }

  onAdd(map) {
    this.map = map;

    // 1. Main Container (Positioning wrapper)
    const container = document.createElement("div");
    container.className = "maplibregl-ctrl relative font-sans pointer-events-auto z-50 w-8 h-8";
    
    // Hidden initially, will be shown in render() if layers exist
    container.classList.add("hidden");

    // 2. The Trigger Button
    const toggleBtn = document.createElement("button");
    toggleBtn.type = "button";
    toggleBtn.className = 
      "w-8.5 h-8.5 bg-white rounded-md shadow-md cursor-pointer " +
      "border-2 border-black/15 hover:bg-gray-100! " +
      "flex items-center justify-center text-gray-600 hover:text-black " +
      "hover:shadow-xl hover:scale-105 transition-all duration-200 ease-out " +
      "focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2";
    toggleBtn.setAttribute("aria-label", "Toggle Layers");
    
    // Icon
    toggleBtn.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
    `;

    toggleBtn.onclick = (e) => {
      e.stopPropagation(); // Prevent immediate closing
      this._togglePanel();
    };

    // 3. The Expanded Panel
    const panel = document.createElement("div");
    panel.className = 
      "absolute top-0 right-0 bg-white " +
      "border border-gray-100 rounded-xl shadow-2xl p-4 min-w-[260px] z-50" +
      "transform origin-top-right transition-all duration-200 ease-out scale-95 opacity-0 pointer-events-none"; // Start hidden/scaled
    
    // Inner HTML Structure
    panel.innerHTML = `
      <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
       <h3 class="text-xs uppercase tracking-widest font-bold text-gray-500 dark:text-gray-400">${this.title}</h3>
        <button type="button" data-close-btn class="text-gray-400 hover:text-gray-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div data-gis-layers-list class="max-h-[300px] overflow-y-auto pr-1 space-y-4 custom-scrollbar"></div>
    `;

    // Close button logic
    panel.querySelector("[data-close-btn]").addEventListener("click", (e) => {
        e.stopPropagation();
        this._togglePanel(false);
    });

    // Assemble
    container.appendChild(toggleBtn);
    container.appendChild(panel);

    this._container = container;
    this._toggleBtn = toggleBtn;
    this._panel = panel;
    this._layerList = panel.querySelector("[data-gis-layers-list]");

    // Initial Render
    this.render();

    return container;
  }

  onRemove() {
    document.removeEventListener("click", this._clickOutsideHandler);
    this._container?.parentNode?.removeChild(this._container);
    this.map = undefined;
  }

  _togglePanel(forceState) {
    const isOpening = forceState !== undefined ? forceState : !this._isExpanded;
    this._isExpanded = isOpening;

    if (isOpening) {
      // Show
      this._panel.classList.remove("scale-95", "opacity-0", "pointer-events-none");
      this._panel.classList.add("scale-100", "opacity-100", "pointer-events-auto");
      this._toggleBtn.classList.add("ring-2", "ring-blue-500", "ring-offset-2"); // Highlight active state
      
      // Add global click listener to close when clicking outside
      setTimeout(() => {
         document.addEventListener("click", this._clickOutsideHandler);
      }, 0);
    } else {
      // Hide
      this._panel.classList.add("scale-95", "opacity-0", "pointer-events-none");
      this._panel.classList.remove("scale-100", "opacity-100", "pointer-events-auto");
      this._toggleBtn.classList.remove("ring-2", "ring-blue-500", "ring-offset-2");
      
      document.removeEventListener("click", this._clickOutsideHandler);
    }
  }

  _handleClickOutside(e) {
    if (!this._container.contains(e.target)) {
        this._togglePanel(false);
    }
  }

  render() {
    if (!this._container || !this._layerList) return;

    const layers = this.manager.getLayersForUI();

    // 1. Hide entire control if empty
    if (!layers || layers.length === 0) {
      this._container.classList.add("hidden");
      return;
    }
    this._container.classList.remove("hidden");

    // 2. Group layers
    const grouped = layers.reduce((acc, l) => {
      const cat = l.category || "General";
      acc[cat] ||= [];
      acc[cat].push(l);
      return acc;
    }, {});

    // 3. Build HTML
    this._layerList.innerHTML = Object.entries(grouped)
      .map(([cat, items]) => {
        const rows = items.map((l) => {
          const checked = l.visible ? "checked" : "";
          
          return `
            <label class="group flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-150 border border-transparent hover:border-gray-100">
              
              <div class="relative flex items-center">
                <input 
                  type="checkbox" 
                  data-layer-key="${l.key}" 
                  ${checked} 
                  class="peer h-4 w-4 rounded bg-white! dark:bg-white! border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                />
              </div>

              <div class="flex items-center gap-2.5 flex-1">
                 <span class="block w-3 h-3 rounded-full shadow-sm ring-1 ring-black/5" style="background:${l.color};"></span>
                 <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900 select-none">${l.display_name}</span>
              </div>
            </label>
          `;
        }).join("");

        return `
          <div>
            <div class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1 pl-2">${cat}</div>
            <div class="grid grid-cols-1 gap-0.5">
                ${rows}
            </div>
          </div>
        `;
      })
      .join("");

    // 4. Bind Events
    this._layerList.querySelectorAll("input[type=checkbox]").forEach((cb) => {
      cb.addEventListener("change", (e) => {
        // Stop bubbling so clicking a checkbox doesn't trigger the "click outside" check immediately if logic was different
        const key = e.target.getAttribute("data-layer-key");
        this.manager.setVisible(key, e.target.checked);
      });
      
      // Prevent click from closing the panel (since it bubbles to document)
      cb.closest("label").addEventListener("click", (e) => e.stopPropagation());
    });
  }
}