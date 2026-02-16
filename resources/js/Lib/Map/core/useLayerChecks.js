export function whenLayerReady(m, layerId, fn) {
    if (!m) return

    // Fast path: layer already exists
    if (m?.getLayer(layerId)) {
        fn(m)
        return
    }

    // Otherwise wait until style updates include the layer
    const onStyleData = () => {
        if (!m.getLayer(layerId)) return
        m.off('styledata', onStyleData)
        fn(m)
    }

    m.on('styledata', onStyleData)
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