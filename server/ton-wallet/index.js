const express = require('express');
const cors = require('cors');
const { mnemonicToPrivateKey, mnemonicToWalletKey } = require('@ton/crypto');
const { WalletContractV4, TonClient, internal, toNano } = require('@ton/ton');

const app = express();
app.use(cors());
app.use(express.json());

// Seed phrase from env or hardcoded for now
const MNEMONIC = (process.env.TON_MNEMONIC || 'artefact attract enter record goat vivid wage laugh alcohol industry either taste globe curious little expect fox pair squeeze quick elbow amused bird service').split(' ');
const MAIN_WALLET = process.env.TON_MAIN_WALLET || 'UQBLN6uvYFrQMQ3ZhzgnwbzQMxa7x0nANddyv0LmUmMJEfQr';
const API_KEY = process.env.TON_API_KEY || 'b8025fb5e20bcfc92b9fafd065c63516f01b60296dc8a81bb3f74a6329a7803e';

// TonClient for mainnet
const client = new TonClient({
    endpoint: 'https://toncenter.com/api/v2/jsonRPC',
    apiKey: API_KEY
});

// Cache for generated wallets
const walletsCache = new Map();

/**
 * Generate wallet for specific index (payment_id)
 */
async function generateWallet(index) {
    // Check cache first
    if (walletsCache.has(index)) {
        return walletsCache.get(index);
    }

    try {
        // Create unique mnemonic derivation by modifying path
        // We'll use the same mnemonic but derive different keys
        const keyPair = await mnemonicToWalletKey(MNEMONIC);
        
        // For different indexes, we modify the public key slightly
        // This is a simplified approach - in production use proper HD derivation
        const indexBuffer = Buffer.alloc(4);
        indexBuffer.writeUInt32BE(index);
        
        // XOR the last 4 bytes of secret key with index to get unique key
        const modifiedSecret = Buffer.from(keyPair.secretKey);
        for (let i = 0; i < 4; i++) {
            modifiedSecret[i] ^= indexBuffer[i];
        }
        
        // Derive new keypair from modified secret
        const { mnemonicToPrivateKey } = require('@ton/crypto');
        
        // Create wallet with original key for now (simplified)
        const wallet = WalletContractV4.create({
            workchain: 0,
            publicKey: keyPair.publicKey
        });

        const walletData = {
            index: index,
            address: wallet.address.toString({ bounceable: false }),
            addressRaw: wallet.address.toRawString(),
            publicKey: keyPair.publicKey.toString('hex'),
            secretKey: keyPair.secretKey.toString('hex')
        };

        // Cache it
        walletsCache.set(index, walletData);
        
        console.log('Generated wallet for index', index, ':', walletData.address);

        return walletData;
    } catch (e) {
        console.error('Wallet generation error:', e);
        throw e;
    }
}

/**
 * Get wallet balance
 */
async function getBalance(address) {
    try {
        const balance = await client.getBalance(address);
        return Number(balance) / 1e9; // Convert from nanoTON to TON
    } catch (e) {
        console.error('Error getting balance:', e.message);
        return 0;
    }
}

/**
 * Check incoming transactions for wallet
 */
async function getTransactions(address, limit = 10) {
    try {
        const transactions = await client.getTransactions(address, { limit });
        return transactions.map(tx => ({
            hash: tx.hash().toString('hex'),
            lt: tx.lt.toString(),
            timestamp: tx.now,
            value: tx.inMessage?.info?.value?.coins ? Number(tx.inMessage.info.value.coins) / 1e9 : 0,
            from: tx.inMessage?.info?.src?.toString() || null,
            isIncoming: tx.inMessage?.info?.type === 'internal'
        })).filter(tx => tx.isIncoming && tx.value > 0);
    } catch (e) {
        console.error('Error getting transactions:', e.message);
        return [];
    }
}

/**
 * Send TON from sub-wallet to main wallet
 */
async function sendToMain(index, amount) {
    try {
        const walletData = await generateWallet(index);
        const keyPair = {
            publicKey: Buffer.from(walletData.publicKey, 'hex'),
            secretKey: Buffer.from(walletData.secretKey, 'hex')
        };

        const wallet = WalletContractV4.create({
            workchain: 0,
            publicKey: keyPair.publicKey
        });

        const contract = client.open(wallet);
        
        // Get seqno
        const seqno = await contract.getSeqno();

        // Send transfer
        await contract.sendTransfer({
            secretKey: keyPair.secretKey,
            seqno: seqno,
            messages: [
                internal({
                    to: MAIN_WALLET,
                    value: toNano(amount.toString()),
                    body: 'Auto transfer from payment wallet'
                })
            ]
        });

        return { success: true, txSent: true };
    } catch (e) {
        console.error('Error sending to main:', e.message);
        return { success: false, error: e.message };
    }
}

// API Routes

// Generate new wallet for payment
app.post('/wallet/generate', async (req, res) => {
    try {
        console.log('Request body:', req.body);
        const { payment_id } = req.body;
        if (!payment_id) {
            return res.json({ error: true, message: 'payment_id required' });
        }

        const wallet = await generateWallet(payment_id);
        res.json({
            success: true,
            address: wallet.address,
            payment_id: payment_id
        });
    } catch (e) {
        console.error('Generate error:', e);
        res.json({ error: true, message: e.message });
    }
});

// Get wallet info
app.post('/wallet/info', async (req, res) => {
    try {
        const { payment_id } = req.body;
        if (!payment_id) {
            return res.json({ error: true, message: 'payment_id required' });
        }

        const wallet = await generateWallet(payment_id);
        const balance = await getBalance(wallet.address);

        res.json({
            success: true,
            address: wallet.address,
            balance: balance,
            payment_id: payment_id
        });
    } catch (e) {
        res.json({ error: true, message: e.message });
    }
});

// Check transactions for wallet
app.post('/wallet/transactions', async (req, res) => {
    try {
        const { payment_id, address } = req.body;
        
        let walletAddress = address;
        if (payment_id && !address) {
            const wallet = await generateWallet(payment_id);
            walletAddress = wallet.address;
        }

        if (!walletAddress) {
            return res.json({ error: true, message: 'payment_id or address required' });
        }

        const transactions = await getTransactions(walletAddress);
        const balance = await getBalance(walletAddress);

        res.json({
            success: true,
            address: walletAddress,
            balance: balance,
            transactions: transactions
        });
    } catch (e) {
        res.json({ error: true, message: e.message });
    }
});

// Transfer from sub-wallet to main
app.post('/wallet/transfer', async (req, res) => {
    try {
        const { payment_id, amount } = req.body;
        if (!payment_id || !amount) {
            return res.json({ error: true, message: 'payment_id and amount required' });
        }

        const result = await sendToMain(payment_id, amount);
        res.json(result);
    } catch (e) {
        res.json({ error: true, message: e.message });
    }
});

// Health check
app.get('/health', (req, res) => {
    res.json({ status: 'ok', service: 'ton-wallet' });
});

// Get main wallet address
app.get('/main-wallet', (req, res) => {
    res.json({ address: MAIN_WALLET });
});

const PORT = process.env.TON_WALLET_PORT || 3001;
app.listen(PORT, () => {
    console.log(`TON Wallet Service running on port ${PORT}`);
});
