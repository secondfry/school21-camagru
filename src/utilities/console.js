export const initConsole = () => {
  console.mlog = (msg) => {
    if (console.isDevelopmentBuild) {
      return console.log(msg);
    }
  };

  console.merror = (msg) => {
    if (console.isDevelopmentBuild) {
      return console.error(msg);
    }
  };
};
