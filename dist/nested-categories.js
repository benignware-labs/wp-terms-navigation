"use strict";

window.addEventListener('click', function (event) {
  console.log('click', event);
  var toggle = event.target.closest("*[data-toggle='terms']");

  if (toggle) {
    var parentItem = event.target.closest(".terms-item");

    if (parentItem) {
      parentItem.classList.toggle('is-open');
    }
  }
});
