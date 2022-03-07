import yesno from "../";

beforeEach(() => {
  document.body.innerHTML = "";
});

test("shows dialog", () => {
  yesno();
  expect(document.querySelector(".jsyesnodialog__dialog")).not.toBeNull();
});

test("dialog has 2 buttons", () => {
  yesno();
  expect(document.querySelector(".jsyesnodialog__dialog__no")).not.toBeNull();
  expect(document.querySelector(".jsyesnodialog__dialog__yes")).not.toBeNull();
});

test("hides dialog on yes click", () => {
  yesno();
  document.querySelector(".jsyesnodialog__dialog__yes").click();
  setTimeout(() => {
    expect(document.querySelector(".jsyesnodialog__dialog")).toBeNull();
  }, 400);
});

test("hides dialog on no click", () => {
  yesno();
  document.querySelector(".jsyesnodialog__dialog__no").click();
  setTimeout(() => {
    expect(document.querySelector(".jsyesnodialog__dialog")).toBeNull();
  }, 400);
});

test("does not create duplicates", () => {
  yesno();
  yesno();
  expect(document.querySelectorAll(".jsyesnodialog__dialog").length).toEqual(1);
});

test("resolves true on yes click", () => {
  yesno().then((yes) => {
    expect(yes).toBe(true);
  });
  document.querySelector(".jsyesnodialog__dialog__yes").click();
});

test("resolves false on no click", () => {
  yesno().then((yes) => {
    expect(yes).toBe(false);
  });
  document.querySelector(".jsyesnodialog__dialog__no").click();
});
