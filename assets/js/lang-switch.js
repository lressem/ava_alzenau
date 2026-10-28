// Language switcher: only the current flag is visible; clicking it unfolds the other languages.
(function () {
  var switcher = document.querySelector('.lang-switch');
  if (!switcher) return;

  var currentLink = switcher.querySelector('.current-lang a');
  if (!currentLink) return;

  function setOpen(isOpen) {
    switcher.classList.toggle('is-open', isOpen);
    currentLink.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  currentLink.setAttribute('aria-haspopup', 'true');
  setOpen(false);

  currentLink.addEventListener('click', function (event) {
    event.preventDefault();
    setOpen(!switcher.classList.contains('is-open'));
  });

  document.addEventListener('click', function (event) {
    if (!switcher.contains(event.target)) setOpen(false);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') setOpen(false);
  });
})();
