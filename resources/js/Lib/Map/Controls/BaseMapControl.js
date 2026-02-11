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

        const container = document.createElement("div");
        container.className = "maplibregl-ctrl z-40";
        container.style.position = "relative";

        const previewBox = document.createElement("button");
        previewBox.type = "button";
        previewBox.className =
            "basemap-preview relative w-16 h-16 rounded-xl overflow-hidden cursor-pointer " +
            "border-2 border-white dark:border-brand-700 " +
            "shadow-md hover:shadow-xl hover:scale-105 transition-all duration-100 ease-out " +
            "focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 z-50";

        previewBox.setAttribute("aria-label", "Change map style");

        const previewImg = document.createElement("div");
        previewImg.className = "w-full h-full bg-center bg-no-repeat bg-cover";
        previewBox.appendChild(previewImg);

        const expandedPanel = document.createElement("div");
        expandedPanel.className =
            "absolute top-0 right-0 bg-white dark:bg-gray-900 " +
            "border border-gray-200 dark:border-gray-800 " +
            "rounded-xl shadow-2xl p-3 min-w-[260px] z-50 " +
            "transform origin-top-right transition-all duration-200";
        expandedPanel.style.display = "none";

        const header = document.createElement("div");
        header.className = "flex items-center justify-between mb-3 px-1";

        const panelTitle = document.createElement("h3");
        panelTitle.className = "text-sm font-bold text-gray-800 dark:text-gray-100";
        panelTitle.textContent = "Map Style";

        header.appendChild(panelTitle);
        expandedPanel.appendChild(header);

        const layerGrid = document.createElement("div");
        layerGrid.className = "grid grid-cols-2 gap-3";

        this._layers.forEach(layer => {
            const optionBtn = document.createElement("button");
            optionBtn.type = "button";
            optionBtn.className =
                "relative group flex flex-col items-center cursor-pointer text-left w-full " +
                "focus:outline-none rounded-lg";

            const imgContainer = document.createElement("div");
            imgContainer.className =
                "w-full h-20 rounded-lg overflow-hidden mb-2 relative " +
                "border-2 transition-all duration-200 ease-in-out " +
                "group-hover:shadow-md";

            const imgDisplay = document.createElement("div");
            imgDisplay.className =
                "w-full h-full bg-center bg-no-repeat bg-cover transition-transform duration-300 group-hover:scale-110";
            imgDisplay.style.backgroundImage = `url('${layer.preview || ""}')`;

            const checkOverlay = document.createElement("div");
            checkOverlay.className =
                "absolute inset-0 bg-brand-600/20 hidden flex items-center justify-center";
            checkOverlay.innerHTML =
                `<svg class="w-6 h-6 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>`;

            imgContainer.appendChild(imgDisplay);
            imgContainer.appendChild(checkOverlay);

            const label = document.createElement("span");
            label.className =
                "text-xs font-medium text-gray-600 dark:text-gray-400 " +
                "group-hover:text-gray-900 dark:group-hover:text-white transition-colors";
            label.textContent = layer.name;

            optionBtn.appendChild(imgContainer);
            optionBtn.appendChild(label);

            optionBtn.addEventListener("click", e => {
                e.stopPropagation();
                this._selectLayer(layer.id);
            });

            layerGrid.appendChild(optionBtn);

            layer.elements = {
                btn: optionBtn,
                container: imgContainer,
                check: checkOverlay,
                label: label
            };
        });

        expandedPanel.appendChild(layerGrid);

        previewBox.addEventListener("click", (e) => {
            e.stopPropagation();
            this._toggle();
        });

        document.addEventListener("click", this._handleDocumentClick);
        expandedPanel.addEventListener("click", e => e.stopPropagation());

        container.appendChild(previewBox);
        container.appendChild(expandedPanel);

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

    _handleDocumentClick() {
        if (this._isExpanded) this._collapse();
    }

    _toggle() {
        this._isExpanded ? this._collapse() : this._expand();
    }

    _expand() {
        this._isExpanded = true;
        this._expandedPanel.style.display = "block";
    }

    _collapse() {
        this._isExpanded = false;
        this._expandedPanel.style.display = "none";
    }

    _selectLayer(id) {
        this._setActiveLayer(id);
        this._updateUI();
        this._collapse();
    }

    _getActiveLayerId() {
        const active = this._layers.find(layer => {
            if (layer.type === "vector" && layer.layerIds) {
                return layer.layerIds.some(layerId => {
                    const l = this._map.getLayer(layerId);
                    return l && this._map.getLayoutProperty(layerId, "visibility") === "visible";
                });
            }
            const l = this._map.getLayer(layer.id);
            return l && this._map.getLayoutProperty(layer.id, "visibility") === "visible";
        });
        return active?.id || this._defaultLayerId;
    }

    _updateUI() {
        const activeId = this._getActiveLayerId();
        const activeLayer = this._layers.find(l => l.id === activeId);

        if (activeLayer) {
            this._previewImg.style.backgroundImage = `url('${activeLayer.preview || ""}')`;
        }

        this._layers.forEach(layer => {
            if (!layer.elements) return;

            const isActive = layer.id === activeId;
            const { container, check, label } = layer.elements;

            if (isActive) {
                container.classList.remove("border-transparent", "border-gray-200", "dark:border-gray-700");
                container.classList.add(
                    "border-brand-600",
                    "ring-2",
                    "ring-brand-600",
                    "ring-offset-1",
                    "ring-offset-white",
                    "dark:ring-offset-gray-900"
                );

                check.classList.remove("hidden");

                label.classList.add("text-brand-600", "dark:text-brand-400", "font-bold");
                label.classList.remove("text-gray-600", "dark:text-gray-400");
            } else {
                container.classList.add("border-gray-200", "dark:border-gray-700");
                container.classList.remove(
                    "border-brand-600",
                    "ring-2",
                    "ring-brand-600",
                    "ring-offset-1",
                    "ring-offset-white",
                    "dark:ring-offset-gray-900"
                );

                check.classList.add("hidden");

                label.classList.remove("text-brand-600", "dark:text-brand-400", "font-bold");
                label.classList.add("text-gray-600", "dark:text-gray-400");
            }
        });
    }

    _setActiveLayer(activeId) {
        if (!this._map) return;
        this._layers.forEach(layer => {
            const visibility = layer.id === activeId ? "visible" : "none";
            if (layer.type === "vector" && layer.layerIds) {
                layer.layerIds.forEach(layerId => {
                    if (this._map.getLayer(layerId)) {
                        this._map.setLayoutProperty(layerId, "visibility", visibility);
                    }
                });
            } else {
                if (this._map.getLayer(layer.id)) {
                    this._map.setLayoutProperty(layer.id, "visibility", visibility);
                }
            }
        });
    }

    onRemove() {
        document.removeEventListener("click", this._handleDocumentClick);
        if (this._container && this._container.parentNode) {
            this._container.parentNode.removeChild(this._container);
        }
        this._map = null;
        this._container = null;
    }
}
