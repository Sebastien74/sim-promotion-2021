/**
 * Preview
 */
export default function () {

    let isEmpty = function (str) {
        return typeof str === 'string' && !str.trim() || typeof str === 'undefined' || str === null;
    }

    let overview = $('#google-preview');
    let prism = $('#highlight-preview');
    let form = $('body form');
    let canonicalPattern = overview.data('canonical-pattern');

    let title = $.trim(form.find(".meta-title").val());
    if (isEmpty(title)) {
        title = overview.attr('data-title');
    }

    let titleAfterDash = '';
    let afterDashActive = overview.attr('data-dash-active');
    let titleAfterDashOverview = overview.attr('data-dash');

    if (afterDashActive) {
        titleAfterDash = $.trim(form.find(".meta-title-second").val());
        if (titleAfterDash || titleAfterDashOverview) {
            if (isEmpty(titleAfterDash)) {
                titleAfterDash = " - " + titleAfterDashOverview;
            } else {
                titleAfterDash = " - " + titleAfterDash;
            }
        }
    }

    let canonical = $.trim(form.find(".meta-canonical").val());
    if (isEmpty(canonical)) {
        canonical = overview.attr('data-canonical');
    } else {
        canonical = canonicalPattern + canonical;
    }

    let description = $.trim(form.find(".meta-description").val());
    if (isEmpty(description)) {
        description = overview.attr('data-description');
    }

    let ogTitle = $.trim(form.find(".meta-og-title").val());
    if (isEmpty(ogTitle)) {
        ogTitle = overview.attr('data-og-title');
    }

    let ogDescription = $.trim(form.find(".meta-og-description").val());
    if (isEmpty(ogDescription)) {
        ogDescription = overview.attr('data-og-description');
    }

    overview.find(".seo-title span.title").html(title);
    overview.find(".seo-title span.title-dash").html(titleAfterDash);
    overview.find(".seo-canonical").html(canonical);
    overview.find(".seo-description").html(description);

    prism.find('.highlight-title').html('&lt;title>' + title + titleAfterDash + '&lt;/title>');
    prism.find('.highlight-description').html('&lt;meta name="description" content="' + description + '" />');
    prism.find('.highlight-og-title').html('&lt;meta property="og:title" content="' + ogTitle + '" />');
    prism.find('.highlight-og-description').html('&lt;meta property="og:description" content="' + ogDescription + '" />');
    prism.find('.highlight-canonical').html('&lt;link rel="canonical" href="' + canonical + '" />');
    prism.find('.highlight-og-url').html('&lt;meta property="og:url" content="' + canonical + '" />');

    Prism.highlightElement($('.highlight-title')[0]);
    Prism.highlightElement($('.highlight-description')[0]);
    Prism.highlightElement($('.highlight-canonical')[0]);
    Prism.highlightElement($('.highlight-og-title')[0]);
    Prism.highlightElement($('.highlight-og-description')[0]);
    Prism.highlightElement($('.highlight-og-url')[0]);
}