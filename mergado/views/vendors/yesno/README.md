# Javascript yes no dialog
![Build status](https://img.shields.io/github/workflow/status/SidKH/yesno-dialog/Node.js%20Package?style=flat-square)
![License](https://img.shields.io/npm/l/yesno-dialog?style=flat-square)
![Code style prettier](https://img.shields.io/badge/code%20style-prettier-%23ff69b4?style=flat-square)

Tiny customizable vanilla js confirm dialog

## Install
```bash
npm install --save yesno-dialog
```

## Usage

```javascript
import yesno from "yesno-dialog";

showCatButton.addEventListener('click', async () => {
  const yes = await yesno()
  if (yes) // show the cat
})
```

![Js yes no dialog example](https://media.giphy.com/media/hU3cAfagRZjnYQXfma/giphy.gif)
  
## Customization  

```javascript
import yesno from "yesno-dialog";

showCatButton.addEventListener('click', async () => {
  const yes = await yesno({
    labelYes: "Yep",
    labelNo: "Nope",
    bodyText: "Sure?"
  })
  if (yes) // show the cat
})
```

```css
.jsyesnodialog {
  --color: #e91e63;
}
```

![Js yes no dialog customize](https://media.giphy.com/media/fstiPkSa2v8viv7k0z/giphy.gif)

## Codesandbox examples
- [Initial](https://codesandbox.io/s/yesno-dialog-example-simple-it5om)
- [Change theme](https://codesandbox.io/s/yesno-dialog-example-theme-gskh0)
- [Customize text](https://codesandbox.io/s/yesno-dialog-example-custom-9lzcd)

## License
[MIT](https://github.com/SidKH/yesno-dialog/blob/master/LICENSE) © SidKH
