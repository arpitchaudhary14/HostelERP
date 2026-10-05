const { app, BrowserWindow, shell } = require('electron');
const path = require('path');

const LIVE_SITE_URL = 'https://hostelerp.eastasia.cloudapp.azure.com';
const PROTOCOL = 'hostelerp';

let mainWindow = null;

// Register custom protocol hostelerp://
if (process.defaultApp) {
  if (process.argv.length >= 2) {
    app.setAsDefaultProtocolClient(PROTOCOL, process.execPath, [path.resolve(process.argv)]);
  }
} else {
  app.setAsDefaultProtocolClient(PROTOCOL);
}

// Ensure Single Instance Lock (Windows/Linux)
const gotTheLock = app.requestSingleInstanceLock();

if (!gotTheLock) {
  app.quit();
} else {
  app.on('second-instance', (event, commandLine) => {
    if (mainWindow) {
      if (mainWindow.isMinimized()) mainWindow.restore();
      mainWindow.focus();
    }
    // Windows/Linux deep link URL parsing
    const url = commandLine.pop();
    handleDeepLink(url);
  });

  app.whenReady().then(() => {
    createWindow();
  });
}

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    title: 'HostelERP Desktop',
    icon: path.join(__dirname, 'assets/icon.png'),
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true
    }
  });

  mainWindow.loadURL(LIVE_SITE_URL);

  // Security: Handle external links (open in user's default browser)
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (url.includes('google.com') || url.includes('microsoft.com') || !url.includes('azurecom')) {
      shell.openExternal(url);
      return { action: 'deny' };
    }
    return { action: 'allow' };
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

// macOS Deep Linking Handler
app.on('open-url', (event, url) => {
  event.preventDefault();
  handleDeepLink(url);
});

// Deep Link Token Extraction & Navigation
function handleDeepLink(deepLinkUrl) {
  if (!deepLinkUrl || !deepLinkUrl.startsWith(`${PROTOCOL}://`)) return;

  try {
    // Example Deep Link: hostelerp://auth-callback?session_token=XYZ123
    const urlObj = new URL(deepLinkUrl);
    const token = urlObj.searchParams.get('session_token');

    if (token && mainWindow) {
      // Redirect app to backend login handler
      mainWindow.loadURL(`${LIVE_SITE_URL}/auth_callback.php?session_token=${token}`);
      mainWindow.focus();
    }
  } catch (err) {
    console.error('Failed to parse deep link:', err);
  }
}

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
