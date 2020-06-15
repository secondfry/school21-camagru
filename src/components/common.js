const initLikes = () => {
  const likeButtons = document.querySelectorAll('.sf-action-like');

  likeButtons.forEach((lb) =>
    lb.addEventListener('click', (e) => {
      e.preventDefault();

      if (!userLogon) {
        document.location.href = '/?action=view&page=login';
      }

      const id = lb.dataset['id'];
      fetch('/?action=like_toggle', {
        method: 'POST',
        body: JSON.stringify({
          id,
        }),
      })
        .then((res) => {
          res
            .text()
            .then(console.mlog.bind(console))
            .catch(console.merror.bind(console));
          // document.location.href = document.location.href;
        })
        .catch(console.merror.bind(console));
    })
  );
};

export const initCommon = () => {
  initLikes();
};
