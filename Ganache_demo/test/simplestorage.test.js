const SimpleStorage = artifacts.require("SimpleStorage");
contract("SimpleStorage", (accounts) => {
  let instance;

  before(async () => {
    instance = await SimpleStorage.deployed();
  });

  it("giá trị ban đầu phải bằng 0", async () => {
    const val = await instance.get();
    assert.equal(val.toString(), "0");
  });

  it("set() cập nhật giá trị đúng", async () => {
    await instance.set(100, { from: accounts[0] });
    const val = await instance.get();
    assert.equal(val.toString(), "100");
  });
});
