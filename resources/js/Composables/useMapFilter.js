import { inject, provide, ref } from "vue"

const MapSymbol = Symbol('MapFilter')

export function useMapFilterProvider() {
    const currentSelection = ref('type')


    const setSelection = (value) => {
        currentSelection.value = value
    }

    const context = {
        currentSelection,
    }

    provide(MapSymbol, context)

    return context

}


export function useMapFilter() {
  const context = inject(MapSymbol)
  if (!context) {
    throw new Error(
      'useMapFilter must be used within a component that has MapFilterProvider as an ancestor'
    )
  }
  return context
}