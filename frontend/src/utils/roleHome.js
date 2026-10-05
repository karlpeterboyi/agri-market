/** Map role → default dashboard path */
export function homeForRole(role) {
  switch (role) {
    case "admin":
      return "/admin";
    case "farmer":
      return "/farmer";
    case "buyer":
      return "/buyer";
    case "provider":
      return "/provider";
    case "agrodealer":
      return "/agrodealer";
    case "processor":
      return "/processor";
    case "transporter":
      return "/transporter";
    case "financier":
      return "/finance/institution";
    case "educator":
      return "/knowledge/educator";
    default:
      return "/";
  }
}
