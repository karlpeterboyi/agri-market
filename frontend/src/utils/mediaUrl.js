/**
 * Browser URL for files on Laravel public disk.
 */
export function mediaUrl(path) {
  if (!path) return null;
  const p = String(path).trim();
  if (!p) return null;
  if (/^https?:\/\//i.test(p) || p.startsWith("data:") || p.startsWith("blob:")) {
    return p;
  }

  let rel = p.replace(/^\/+/, "");
  if (rel.startsWith("storage/")) rel = rel.slice(8);
  if (rel.startsWith("public/")) rel = rel.slice(7);

  // Same-origin /storage works with Vite proxy + php artisan storage:link
  if (typeof window !== "undefined") {
    return `${window.location.origin}/storage/${rel}`;
  }

  const api = (import.meta.env.VITE_API_URL || "").replace(/\/api\/?$/, "").replace(/\/$/, "");
  return `${api || ""}/storage/${rel}`;
}

export function listingCover(item) {
  if (!item) return null;
  return (
    mediaUrl(item.featured_image_url) ||
    mediaUrl(item.featured_image) ||
    mediaUrl(item.cover_photo) ||
    mediaUrl(item.image_2) ||
    mediaUrl(item.image) ||
    null
  );
}
