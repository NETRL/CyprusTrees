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
        if (!hasLayer()) return
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

function isStyleReady(m) {
    try {
        return !!m && !!m.getStyle?.(); // getStyle() returns a style object when ready
    } catch {
        return false;
    }
}