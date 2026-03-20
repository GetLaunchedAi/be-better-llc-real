/**
 * Eleventy config
 * - Input:  src
 * - Output: _site
 * - Templates: Nunjucks
 */
module.exports = function (eleventyConfig) {
  // Copy static assets straight through to the output folder
  eleventyConfig.addPassthroughCopy({ "src/assets": "assets" });
  eleventyConfig.addPassthroughCopy({ "src/.htaccess": ".htaccess" });
  eleventyConfig.addPassthroughCopy({ "src/_laravel.php": "_laravel.php" });

  // Minimal helper for footer copyright year
  eleventyConfig.addShortcode("year", () => String(new Date().getFullYear()));


  // Filters
  // - filter product catalog by collection/tag/category
  eleventyConfig.addFilter("filterByCollection", (items, key) => {
    if (!Array.isArray(items)) return [];
    if (!key) return items;

    const needle = String(key).toLowerCase();

    return items.filter((item) => {
      if (!item) return false;

      const collections = item.collections || item.collection || item.categories || [];
      const tags = item.tags || [];
      const category = item.category;

      const hasNeedle = (arr) =>
        Array.isArray(arr) && arr.map((v) => String(v).toLowerCase()).includes(needle);

      if (hasNeedle(collections) || hasNeedle(tags)) return true;
      if (category && String(category).toLowerCase() === needle) return true;

      return false;
    });
  });


  // Filter: JSON stringify for client-side feeds
  eleventyConfig.addFilter("jsonify", (value, spaces = 0) => {
    try {
      return JSON.stringify(value, null, spaces);
    } catch (e) {
      return "[]";
    }
  });

  return {
    dir: {
      input: "src",
      includes: "_includes",
      output: "_site"
    },
    templateFormats: ["njk", "md", "html"],
    htmlTemplateEngine: "njk",
    markdownTemplateEngine: "njk",
    dataTemplateEngine: "njk"
  };
};
