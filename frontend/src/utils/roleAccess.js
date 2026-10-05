export function canSellProduce(role) {
  return ["farmer", "admin"].includes(role);
}
export function canSellInputs(role) {
  return ["agrodealer", "admin"].includes(role);
}
export function canSellMachinery(role) {
  return ["farmer", "admin"].includes(role);
}
export function canSellServices(role) {
  return ["provider", "admin"].includes(role);
}
export function isAdmin(role) {
  return role === "admin";
}
