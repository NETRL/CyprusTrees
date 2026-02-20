export function useStoredPrefs() {
  function getPref(key, defaultValue = null) {
    const raw = localStorage.getItem(key)
    if (raw !== null) {
      try {
        return JSON.parse(raw)
      } catch {
        return raw
      }
    }
    if (defaultValue !== null) {
      localStorage.setItem(key, JSON.stringify(defaultValue))
    }
    return defaultValue
  }

  function setPref(key, value) {
    localStorage.setItem(key, JSON.stringify(value))
  }

  return { getPref, setPref }
}
