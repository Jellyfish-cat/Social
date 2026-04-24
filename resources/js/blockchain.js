import Web3 from 'web3';
import SimpleStorageABI from './abi/SimpleStorage.json';

async function initBlockchain() {
    const statsContainer = document.getElementById('blockchain-stats');
    const displayElement = document.getElementById('blockchain-value');

    if (!statsContainer || !displayElement) return;

    const profileId = parseInt(statsContainer.getAttribute('data-profile-id'));
    const authMeta = document.querySelector('meta[name="auth-user-id"]');
    // Nếu chưa đăng nhập, mặc định viewerId là 0
    const currentUserId = authMeta ? parseInt(authMeta.content) : 0;

    try {
        const web3 = new Web3('http://127.0.0.1:7545');
        const networkId = (await web3.eth.net.getId()).toString();
        const deployedNetwork = SimpleStorageABI.networks[networkId];

        if (!deployedNetwork) {
            displayElement.innerText = "Check Migrations";
            return;
        }

        const contract = new web3.eth.Contract(SimpleStorageABI.abi, deployedNetwork.address);
        const accounts = await web3.eth.getAccounts();
        const mainAccount = accounts[0];

        if (profileId && profileId !== currentUserId) {
            const alreadyViewed = await contract.methods.hasViewed(profileId, currentUserId).call();

            if (!alreadyViewed) {
                console.log("Lượt xem mới! Đang gửi giao dịch lên Blockchain...");
                try {
                    await contract.methods.incrementView(profileId, currentUserId).send({
                        from: mainAccount,
                        gas: 200000
                    });
                    console.log("Đã ghi nhận lượt xem mới cho ID:", profileId);
                } catch (e) {
                    console.warn("Giao dịch bị từ chối/Lỗi:", e.message);
                }
            } else {
                console.log("Hệ thống Blockchain: Bạn đã xem Profile này rồi, không tăng thêm số.");
            }
        }

        const viewsCount = await contract.methods.profileViews(profileId).call();
        displayElement.innerText = viewsCount.toString();

    } catch (error) {
        console.error("Blockchain Error:", error);
        displayElement.innerText = "Error";
    }
}

document.addEventListener('DOMContentLoaded', initBlockchain);
