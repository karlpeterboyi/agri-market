/**
 * Unwrap Laravel paginator / resource / plain array responses.
 */
export function unwrapList(payload) {
  if (!payload) return [];
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload.data)) return payload.data;
  if (payload.data && Array.isArray(payload.data.data)) return payload.data.data;
  return [];
}

export function unwrapItem(payload) {
  if (!payload) return null;
  if (payload.data && !Array.isArray(payload.data) && typeof payload.data === "object") {
    // Resource: { data: { id: ... } }
    if (payload.data.data && typeof payload.data.data === "object" && !Array.isArray(payload.data.data)) {
      return payload.data.data;
    }
    return payload.data;
  }
  return payload;
}
