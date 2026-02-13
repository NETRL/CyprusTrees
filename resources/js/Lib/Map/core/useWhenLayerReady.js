export function whenLayerReady(m, layerId, fn) {
    if (!m) return

    // Fast path: layer already exists
    if (m.getLayer(layerId)) {
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