/**
 * @see https://github.com/swissspidy/disable-embeds/blob/a394b6fd42bc1d504f7a0909fa72ad6b19e02856/src/index.js
 */

(function () {
  wp.domReady(() => {
    // Only unregister WordPress embed only.
    if (wp.blocks.getBlockVariations && wp.blocks.getBlockVariations('core/embed')) {
      wp.blocks.unregisterBlockVariation('core/embed', 'wordpress');
    }
    else if (wp.blocks.getBlockType('core-embed/wordpress')) {
      wp.blocks.unregisterBlockType('core-embed/wordpress');
    }

    /*
		// Unregister all embed blocks
		wp.blocks.getBlockVariations('core/embed').forEach(function (blockVariation) {
			wp.blocks.unregisterBlockVariation('core/embed', blockVariation.name)
		})
		 */
  });
})();
