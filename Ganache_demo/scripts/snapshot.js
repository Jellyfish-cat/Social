module.exports = async function (callback) {
  try {
    const snapshotId = await new Promise((resolve, reject) => {
      web3.currentProvider.send(
        {
          jsonrpc: "2.0",
          method: "evm_snapshot",
          params: [],
          id: new Date().getTime()
        },
        (err, result) => {
          if (err) return reject(err);
          resolve(result.result);
        }
      );
    });

    console.log("Snapshot ID:", snapshotId);

    callback();
  } catch (err) {
    console.error("Error:", err);
    callback(err);
  }
};