document.addEventListener('DOMContentLoaded', function () {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);

  mmp_GA4_helpers.events.sendSearch(urlParams.get('search_query'));
})
