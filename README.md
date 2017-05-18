<head>
<meta http-equiv="X-UA-Compatible" content="${head.language}" charset="utf-8"
      data-sly-use.head="head.js"
      data-sly-use.clientLib="${'/libs/granite/sightly/templates/clientlib.html'}">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
<meta name="keywords" content="${head.keywords}"/>

<sly data-sly-include="author.html" />
<sly data-sly-include="/libs/cq/cloudserviceconfigs/components/servicelibs/servicelibs.jsp" />
<sly data-sly-include="customheaderlibs.html" />
<link data-sly-test="${head.faviconPath}" rel="icon" type="image/vnd.microsoft.icon" href="${head.faviconPath}">
<link data-sly-test="${head.faviconPath}" rel="shortcut icon" type="image/vnd.microsoft.icon" href="${head.faviconPath}">

<link rel="alternate" href="https://www.alfaromeousa.com${head.currentPagePathWithoutDomain}" hreflang="en-us"/> 
<link rel="alternate" href="https://www.alfaromeo.ca${head.currentPagePathWithoutDomain}" hreflang="en-ca"/> 
<link rel="alternate" href="https://www.alfaromeo.ca${head.currentPagePathWithoutDomain}" hreflang="fr-ca"/> 
<link rel="alternate" href="https://es.alfaromeousa.com${head.currentPagePathWithoutDomain}" hreflang="es-us"/>

<link data-sly-test="${head.isInBlackList != 'true'}" rel="canonical" href="${head.currentPagePath}">

<title>${head.pageTitle || head.title}</title>
<meta name="title" content="${head.pageTitle || head.title}" />
<meta name="description" content="${head.description}" />

<!-- DEFAULT::SOCIALMETA **************************************** -->
<social data-sly-use.socialmeta="socialmeta.html" data-sly-unwrap>
 <sly data-sly-call="${socialmeta.google @ title=(head.pageTitle || head.title), description=head.description, info=head.social}" />
 <sly data-sly-call="${socialmeta.twitter @ title=(head.pageTitle || head.title), description=head.description, info=head.social}" />
 <sly data-sly-call="${socialmeta.facebook @ title=(head.pageTitle || head.title), description=head.description, info=head.social}" />
</social>
<!-- END DEFAULT::SOCIALMETA **************************************** -->
	
<!-- CUSTOM SCRIPT **************************************** -->
${head.headCustomScript @ context='unsafe'}
<!-- END CUSTOM SCRIPT **************************************** -->

<script>var digitalData = ${head.jsonDataLayer @ context='unsafe'};</script>

<sly data-sly-include="initjs.html" />
<sly data-sly-include="headext.html" />
    <link rel="stylesheet" type="text/css" href="/ar_nafta/superbowl/sliderManager.${properties['info-countryCode']}.css?_t=${head.ts @ context='unsafe'}" media="screen"/>
<script type="text/javascript" data-sly-test="${wcmmode.disabled && head.social.socialRedirect}">
 window.location.href = "${head.social.socialRedirect @ context='scriptString'}";
</script>
</head>