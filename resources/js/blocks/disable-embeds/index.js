(function () {
  wp.domReady(() => {
    // Only unregister WordPress embed only.
    if (wp.blocks.getBlockVariations && wp.blocks.getBlockVariations('core/embed')) {
      wp.blocks.unregisterBlockVariation('core/embed', 'wordpress');
    }
    else if (wp.blocks.getBlockType('core-embed/wordpress')) {
      wp.blocks.unregisterBlockType('core-embed/wordpress');
    }
  });
})();
