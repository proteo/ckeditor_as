/**
 * @file
 * Custom settings for CKEditor.
 */


CKEDITOR.editorConfig = function(config) {
  config.pasteFilter = 'semantic-content';
};

CKEDITOR.on('instanceReady', function(ev) {
  // Adds HTML id to editor's body element so we can hook up our styles.
  ev.editor.document.getBody().setAttributes({'id':'ckeditor-body'});

  // On paste, we want remove unwanted styling. This is performed by default if
  // we use the pasteFilter with the 'semantic-content' setting (see above).
  if (!ev.editor.pasteFilter) {
    ev.editor.pasteFilter = new CKEDITOR.filter();
  }
  // Additional filter options.
  ev.editor.pasteFilter.disallow('*{*}');
  ev.editor.pasteFilter.disallow('code; span');
  ev.editor.pasteFilter.disallow('p[*]; h1[*]; h2[*]; h3[*]; h4[*]; h5[*]; h6[*]; table[*]; thead[*]; tbody[*]; tr[*]; th[*], td[*]');

  // Filter for specific HTML elements.
  ev.editor.dataProcessor.htmlFilter.addRules({
    elements: {
      table: function(element) {
        // Add "table" class.
        if (element.attributes.class) {
          // Class attribute exists, check if we need to add "table".
          var pieces = element.attributes.class.split(' ');
          if (pieces.indexOf('table') < 0) {
            pieces.push('table');
            element.attributes.class = pieces.join(' ');
          }
        } else {
          element.attributes.class = 'table';
        }
      }
    }
  },{applyToAll: true});

  // Performs some cleanup on paste.
  ev.editor.on('paste', function(ev) {
    var value = CkTextCleanup(ev.data.dataValue);
    ev.data.dataValue = CkReplaceAnchorTarget(value);
  }, null, null, 9);
});

/**
 * Removes extra spaces and empty paragraphs.
 */
function CkTextCleanup(input) {
  // Replace HTML space entities with a single space.
  var text = input.replace(/(\u00a0)+/g, ' ');
  // Create new paragraphs out of two line breaks.
  text = text.replace(/<br(\s?\/?)>(\s)*<br(\s?\/?)>/g, '</p><p>');
  // Delete paragraphs that are empty or contain only spaces or line breaks.
  return text.replace(/<(p|div)\b[^>]*>(\s|\n|\r\n|\u00a0|<br(\s?\/?)>)*<\/(p|div)>/g, '');
}

/**
 * Adds target="_blank" to external links.
 */
function CkReplaceAnchorTarget(input) {
  var parser = new DOMParser();
  var d = parser.parseFromString(input, 'text/html');
  var a = d.getElementsByTagName('a');

  // Build a map of regular expresions for each available host.
  const r = drupalSettings.ckeditor.siteHosts.map(host => new RegExp(host + "$", "i"));

  for (var anchor of a) {
    var href = anchor.host;
    var match = false;
    r.forEach(exp => {
      if (exp.test(href)) {
        match = true;
      }
    });
    if (match) {
      anchor.removeAttribute('target');
    } else {
      anchor.setAttribute('target', '_blank');
      anchor.setAttribute('rel', 'noopener');
    }
  }

  return d.body.innerHTML;
}
