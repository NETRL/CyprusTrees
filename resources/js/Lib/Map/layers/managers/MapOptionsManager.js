import { MapOptionsControl } from "@/Lib/Map/controls/MapOptionsControl"
import { MAP_MODES, MAP_PANELS } from "@/Lib/Map/useMapUiState"

export class MapOptionsManager {
    constructor(map, { position = "top-right", mapUi } = {}) {
        this.map = map
        this.position = position
        this.mapUi = mapUi // injected: { ui, openPanel, closePanel, togglePanel, ... }

        this.control = null
    }

    init() {
        if (!this.map) return this

        // guard: don’t add twice
        if (this.control) return this

        this.control = new MapOptionsControl({
            title: "Options",
            position: "top-right",
            onShowEvents: () => {
                this.mapUi.setActiveMode(MAP_MODES.EVENTS)
                this.mapUi.openPanel(MAP_PANELS.EVENTS)
                this.mapUi.closeSidebar()
            },

            onClose: () => {
                this.mapUi.closePanel()
            },

            getActivePanel: () => this.mapUi.ui.activePanel,
        })

        this.map.addControl(this.control, this.position)
        return this
    }

    destroy() {
        if (!this.map || !this.control) return
        try {
            this.map.removeControl(this.control)
        } catch (_) {
            // ignore
        } finally {
            this.control = null
        }
    }
}