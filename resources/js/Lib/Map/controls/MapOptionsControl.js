export class MapOptionsControl {
    constructor({
        title = "Options",
        position = "top-right",

        // actions
        onShowEvents,
        onClose,

        //state getter for UI highlighting
        getActivePanel, // () => string|null
    } = {}) {
        this.title = title
        this.position = position
        this.onShowEvents = onShowEvents
        this.onClose = onClose
        this.getActivePanel = getActivePanel

        this.map = null
        this._container = null
        this._menu = null
        this._btn = null

        this._onDocClick = this._onDocClick.bind(this)
    }

    onAdd(map) {
        this.map = map

        const root = document.createElement("div")
        root.className = "maplibregl-ctrl relative font-sans pointer-events-auto z-60! w-8 h-8"

        root.innerHTML = `
            <button type="button"
            data-map-options-btn
            class="w-8.5 h-8.5 bg-white rounded-md shadow-md cursor-pointer
                border-2 border-black/15 hover:bg-gray-100!
                flex items-center justify-center text-gray-600 hover:text-black
                hover:shadow-xl hover:scale-105 transition-all duration-200 ease-out
                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 32 32" stroke="currentColor" stroke-width="2">
                    <path d="M12.15 28.012v-0.85c0.019-0.069 0.050-0.131 0.063-0.2 0.275-1.788 1.762-3.2 3.506-3.319 1.95-0.137 3.6 0.975 4.137 2.787 0.069 0.238 0.119 0.488 0.181 0.731v0.85c-0.019 0.056-0.050 0.106-0.056 0.169-0.269 1.65-1.456 2.906-3.081 3.262-0.125 0.025-0.25 0.063-0.375 0.094h-0.85c-0.056-0.019-0.113-0.050-0.169-0.056-1.625-0.262-2.862-1.419-3.237-3.025-0.037-0.156-0.081-0.3-0.119-0.444zM20.038 3.988l-0 0.85c-0.019 0.069-0.050 0.131-0.056 0.2-0.281 1.8-1.775 3.206-3.538 3.319-1.944 0.125-3.588-1-4.119-2.819-0.069-0.231-0.119-0.469-0.175-0.7v-0.85c0.019-0.056 0.050-0.106 0.063-0.162 0.3-1.625 1.244-2.688 2.819-3.194 0.206-0.069 0.425-0.106 0.637-0.162h0.85c0.056 0.019 0.113 0.050 0.169 0.056 1.631 0.269 2.863 1.419 3.238 3.025 0.038 0.15 0.075 0.294 0.113 0.437zM20.037 15.575v0.85c-0.019 0.069-0.050 0.131-0.063 0.2-0.281 1.794-1.831 3.238-3.581 3.313-1.969 0.087-3.637-1.1-4.106-2.931-0.050-0.194-0.094-0.387-0.137-0.581v-0.85c0.019-0.069 0.050-0.131 0.063-0.2 0.275-1.794 1.831-3.238 3.581-3.319 1.969-0.094 3.637 1.1 4.106 2.931 0.050 0.2 0.094 0.394 0.137 0.588z"/>
                </svg>
            </button>

        <div data-map-options-menu
            class="absolute top-0 right-0 bg-white
                border border-gray-100 rounded-xl shadow-2xl p-4 min-w-[260px] z-60!
                transform origin-top-right transition-all duration-200 ease-out
                scale-95 opacity-0 pointer-events-none">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                <h3 class="text-xs uppercase tracking-widest font-bold text-gray-500 dark:text-gray-400">Map Options</h3>
                <button type="button" data-action="close" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                </button>
            </div>

            <button type="button" data-action="show-events"
                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md">
                Show Events
            </button>
        </div>
        `

        this._container = root;
        this._btn = root.querySelector("[data-map-options-btn]");
        this._menu = root.querySelector("[data-map-options-menu]");
        this._isOpen = false;

        // IMPORTANT: bind once so removeEventListener works
        this._onDocClick = (e) => {
            if (!this._isOpen) return;
            if (this._container.contains(e.target)) return;
            this.closeMenu();
        };

        this._btn.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleMenu();
        });

        this._menu.addEventListener("click", (e) => {
            const btn = e.target?.closest?.("button[data-action]");
            if (!btn) return;
            e.stopPropagation();
            this._handleAction(btn.getAttribute("data-action"));
        });

        this.syncUi()

        // only add global listener when open (like your other control)
        return root;
    }

    onRemove() {
        document.removeEventListener("click", this._onDocClick, true);
        this._container?.parentNode?.removeChild(this._container);
        this.map = null;
        this._container = this._menu = this._btn = null;
    }

    openMenu() {
        if (!this._menu) return;
        this._isOpen = true;

        this._menu.classList.remove("scale-95", "opacity-0", "pointer-events-none");
        this._menu.classList.add("scale-100", "opacity-100", "pointer-events-auto");
        this._btn.classList.add("ring-2", "ring-blue-500", "ring-offset-2");

        // defer so the opening click doesn’t immediately close it
        setTimeout(() => document.addEventListener("click", this._onDocClick, true), 0);
    }

    closeMenu() {
        if (!this._menu) return;
        this._isOpen = false;

        this._menu.classList.add("scale-95", "opacity-0", "pointer-events-none");
        this._menu.classList.remove("scale-100", "opacity-100", "pointer-events-auto");
        this._btn.classList.remove("ring-2", "ring-blue-500", "ring-offset-2");

        document.removeEventListener("click", this._onDocClick, true);
    }

    toggleMenu() {
        if (this._isOpen) this.closeMenu();
        else this.openMenu();
    }

    _onDocClick(e) {
        // close if click happened outside this control
        if (!this._container) return
        if (this._container.contains(e.target)) return
        this.closeMenu()
    }

    _handleAction(action) {
        this.closeMenu()

        switch (action) {
            case "show-events":
                this.onShowEvents?.()
                break
            case "close":
                this.onClose?.()
                break
            default:
                break
        }
    }

    syncUi() {
        // no-op for now; later you can highlight selected menu item
        // using this.getActivePanel?.()
    }
}