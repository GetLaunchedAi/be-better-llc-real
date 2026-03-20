const DEFAULTS = {
  hero: {
    image: "/assets/img/placeholder.jpg",
    alt: "",
  },
  newArrivals: {
    bannerImage: "/assets/img/placeholder.jpg",
    bannerAlt: "",
  },
  grip: {
    mainImage: "/assets/img/placeholder.jpg",
    mainAlt: "",
    tiles: [
      { image: "/assets/img/placeholder.jpg", alt: "" },
      { image: "/assets/img/placeholder.jpg", alt: "" },
      { image: "/assets/img/placeholder.jpg", alt: "" },
    ],
  },
  holidayDeals: {
    tiles: [
      { image: "/assets/img/OG Better Hoodie Blk and white 2.png", alt: "" },
      { image: "/assets/img/OG Tan Be Better Crew.png", alt: "" },
    ],
  },
  featuredPromos: {
    tiles: [
      { image: "/assets/img/OG BLK Be Better Crew.png", alt: "" },
      { image: "/assets/img/OG Better Hoodie (Katy).png", alt: "" },
      { image: "/assets/img/Women_s Relax crew Bone.png", alt: "" },
    ],
  },
};

function mergeDeep(base, next) {
  if (!next || typeof next !== "object") return base;
  const merged = { ...base };

  Object.keys(next).forEach((key) => {
    const baseVal = merged[key];
    const nextVal = next[key];

    if (Array.isArray(baseVal) && Array.isArray(nextVal)) {
      merged[key] = nextVal.map((item, i) =>
        typeof item === "object" && item !== null
          ? { ...(baseVal[i] || {}), ...item }
          : item
      );
      return;
    }

    if (
      baseVal &&
      typeof baseVal === "object" &&
      !Array.isArray(baseVal) &&
      nextVal &&
      typeof nextVal === "object" &&
      !Array.isArray(nextVal)
    ) {
      merged[key] = mergeDeep(baseVal, nextVal);
      return;
    }

    merged[key] = nextVal;
  });

  return merged;
}

module.exports = async function () {
  const url =
    process.env.HOMEPAGE_CONTENT_URL ||
    "http://127.0.0.1:8000/homepage-content.json";

  try {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 3000);
    const response = await fetch(url, { signal: controller.signal });
    clearTimeout(timer);

    if (!response.ok) {
      return DEFAULTS;
    }

    const payload = await response.json();
    return mergeDeep(DEFAULTS, payload);
  } catch (_error) {
    return DEFAULTS;
  }
};
