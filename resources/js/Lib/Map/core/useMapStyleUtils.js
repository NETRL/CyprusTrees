// Suggested file rename: useMapStyleGuards.js (see naming notes below)

export function whenLayerReady(m, layerId, fn) {
    if (!m) return

    if (hasLayer(m, layerId)) {
        fn(m)
        return
    }

    let done = false
    const cleanup = () => {
        if (done) return
        done = true
        m.off("styledata", onTick)
        m.off("style.load", onTick)
        m.off("load", onTick)
    }

    const onTick = () => {
        if (!hasLayer(m, layerId)) return
        cleanup()
        fn(m)
    }

    // 'load' fires once for initial load; 'style.load' fires after setStyle() too.
    m.on("load", onTick)
    m.on("style.load", onTick)
    m.on("styledata", onTick)
}

export function hasLayer(m, id) {
    try {
        return isStyleReady(m) && !!m.getLayer?.(id);
    } catch {
        return false;
    }
}

export function hasSource(m, id) {
    try {
        return isStyleReady(m) && !!m.getSource?.(id);
    } catch {
        return false;
    }
}


export function getSourceSafe(m, id) {
    try {
        if (!isStyleReady(m)) return null;
        return m.getSource?.(id) ?? null;
    } catch {
        return null;
    }
}

function isStyleReady(m) {
    try {
        // getStyle() returns a style object only once style exists.
        return !!m && typeof m.getStyle === "function" && !!m.getStyle()
    } catch {
        return false
    }
}