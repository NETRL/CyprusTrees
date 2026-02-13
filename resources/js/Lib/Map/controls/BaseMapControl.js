export class BaseMapControl {
    constructor(options) {
        this._layers = options.layers;
        this._defaultLayerId = options.defaultLayerId || (options.layers[0] && options.layers[0].id);
        this._map = options.map;
        this._container = null;
        this._isExpanded = false;
        this._activeId = this._defaultLayerId;
        this._handleDocumentClick = this._handleDocumentClick.bind(this);
    }

    onAdd(map) {
        this._map = map;

        // 1. Container
        const container = document.createElement("div");
        container.className = "maplibregl-ctrl relative z-40 font-sans w-8 h-8";

        // 2. Preview Box (The Toggle)
        const previewBox = document.createElement("button");
        previewBox.type = "button";
        previewBox.className =
            "basemap-preview relative w-8.5 h-8.5 rounded-md overflow-hidden cursor-pointer " +
            "border-2 border-gray-200 dark:border-brand-700 shadow-lg " +
            "hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-200 ease-out " +
            "focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900";
        previewBox.setAttribute("aria-label", "Change map style");

        const previewImg = document.createElement("div");
        previewImg.className = "w-full h-full bg-center bg-no-repeat bg-cover";
        previewBox.appendChild(previewImg);

        // 3. Expanded Panel
        const expandedPanel = document.createElement("div");
        expandedPanel.className =
            "absolute top-0 right-0 bg-white dark:bg-gray-900 " +
            "border border-gray-100 dark:border-gray-800 " +
            "rounded-2xl shadow-2xl p-4 min-w-[280px] z-50 " +
            "transform origin-top-right transition-all duration-200 ease-out " +
            "opacity-0 scale-95 pointer-events-none"; // Initially hidden via transform

        // Header
        const header = document.createElement("div");
        header.className = "flex items-center justify-between mb-4 px-1";
        header.innerHTML = `
            <h3 class="text-xs uppercase tracking-widest font-bold text-gray-500 dark:text-gray-400">Map Style</h3>
            <span class="text-[10px] text-gray-400 font-medium">Select one</span>
        `;
        expandedPanel.appendChild(header);

        // Grid
        const layerGrid = document.createElement("div");
        layerGrid.className = "grid grid-cols-2 gap-3";

        this._layers.forEach((layer) => {
            const optionBtn = document.createElement("button");
            optionBtn.type = "button";
            optionBtn.className = "relative group flex flex-col cursor-pointer w-full rounded-lg focus:outline-none";

            const imgContainer = document.createElement("div");
            imgContainer.className =
                "w-full h-16 rounded-lg overflow-hidden mb-2 relative border-2 " +
                "transition-all duration-200 ease-in-out group-hover:shadow-md";

            const imgDisplay = document.createElement("div");
            imgDisplay.className = "w-full h-full bg-center bg-no-repeat bg-cover transition-transform duration-500 group-hover:scale-110";
            imgDisplay.style.backgroundImage = `url('${layer.preview || ""}')`;

            const checkOverlay = document.createElement("div");
            checkOverlay.className = "absolute inset-0 bg-brand-600/40 opacity-0 transition-opacity duration-200 flex items-center justify-center";
            checkOverlay.innerHTML = `
                <div class="bg-brand-600 rounded-full p-1 shadow-lg transform scale-75 group-active:scale-90 transition-transform">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>`;

            imgContainer.appendChild(imgDisplay);
            imgContainer.appendChild(checkOverlay);

            const label = document.createElement("span");
            label.className = "text-[11px] font-semibold text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors text-center w-full";
            label.textContent = layer.name;

            optionBtn.appendChild(imgContainer);
            optionBtn.appendChild(label);

            optionBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                this._selectLayer(layer.id);
            });

            layerGrid.appendChild(optionBtn);

            layer.elements = {
                btn: optionBtn,
                container: imgContainer,
                check: checkOverlay,
                label: label,
            };
        });

        expandedPanel.appendChild(layerGrid);
        container.appendChild(previewBox);
        container.appendChild(expandedPanel);

        // Event Listeners
        previewBox.addEventListener("click", (e) => {
            e.stopPropagation();
            this._toggle();
        });

        document.addEventListener("click", this._handleDocumentClick);

        this._container = container;
        this._previewBox = previewBox;
        this._previewImg = previewImg;
        this._expandedPanel = expandedPanel;

        if (this._defaultLayerId) {
            this._setActiveLayer(this._defaultLayerId);
        }
        this._updateUI();

        return container;
    }

    _handleDocumentClick(e) {
        if (this._isExpanded && !this._container.contains(e.target)) {
            this._collapse();
        }
    }

    _toggle() {
        this._isExpanded ? this._collapse() : this._expand();
    }

    _expand() {
        this._isExpanded = true;
        this._expandedPanel.classList.remove("opacity-0", "scale-95", "pointer-events-none");
        this._expandedPanel.classList.add("opacity-100", "scale-100", "pointer-events-auto");
        this._previewBox.classList.add("ring-2", "ring-brand-500", "ring-offset-2");
    }

    _collapse() {
        this._isExpanded = false;
        this._expandedPanel.classList.add("opacity-0", "scale-95", "pointer-events-none");
        this._expandedPanel.classList.remove("opacity-100", "scale-100", "pointer-events-auto");
        this._previewBox.classList.remove("ring-2", "ring-brand-500", "ring-offset-2");
    }

    _selectLayer(id) {
        this._setActiveLayer(id);
        this._updateUI();
        // Delay collapse slightly for visual feedback on click
        setTimeout(() => this._collapse(), 150);
    }

    _updateUI() {
        const activeId = this._getActiveLayerId();
        const activeLayer = this._layers.find((l) => l.id === activeId);

        if (activeLayer) {
            this._previewImg.style.backgroundImage = `url('${activeLayer.preview || ""}')`;
        }

        this._layers.forEach((layer) => {
            if (!layer.elements) return;

            const isActive = layer.id === activeId;
            const { container, check, label } = layer.elements;

            if (isActive) {
                container.className = "w-full h-16 rounded-lg overflow-hidden mb-2 relative border-2 border-brand-500 ring-2 ring-brand-500/20 shadow-md";
                check.classList.remove("opacity-0");
                check.classList.add("opacity-100");
                label.className = "text-[11px] font-bold text-brand-600 dark:text-brand-400 text-center w-full";
            } else {
                container.className = "w-full h-16 rounded-lg overflow-hidden mb-2 relative border-2 border-transparent dark:border-gray-800 transition-all duration-200 group-hover:border-gray-300";
                check.classList.remove("opacity-100");
                check.classList.add("opacity-0");
                label.className = "text-[11px] font-semibold text-gray-500 dark:text-gray-400 text-center w-full group-hover:text-gray-800";
            }
        });
    }

    // --- Map logic remains the same ---
    _getActiveLayerId() {
        const active = this._layers.find((layer) => {
            const layerIdToCheck = layer.type === "vector" && layer.layerIds ? layer.layerIds[0] : layer.id;
            const l = this._map.getLayer(layerIdToCheck);
            return l && this._map.getLayoutProperty(layerIdToCheck, "visibility") === "visible";
        });
        return active?.id || this._defaultLayerId;
    }

    _setActiveLayer(activeId) {
        if (!this._map) return;
        this._layers.forEach((layer) => {
            const visibility = layer.id === activeId ? "visible" : "none";
            const ids = layer.type === "vector" && layer.layerIds ? layer.layerIds : [layer.id];
            ids.forEach((id) => {
                if (this._map.getLayer(id)) {
                    this._map.setLayoutProperty(id, "visibility", visibility);
                }
            });
        });
    }

    onRemove() {
        document.removeEventListener("click", this._handleDocumentClick);
        this._container?.remove();
        this._map = null;
    }
}