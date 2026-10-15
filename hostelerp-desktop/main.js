const { app, BrowserWindow, shell, dialog, Menu, ipcMain } = require('electron');
const path = require('path');

const LIVE_SITE_URL = process.env.HOSTELERP_URL || 'https://hostelerp.eastasia.cloudapp.azure.com';
const PROTOCOL = 'hostelerp';

let mainWindow = null;
let isQuitting = false;

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
    setupApplicationMenu();
  });
}

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    minWidth: 980,
    minHeight: 640,
    title: 'HostelERP Desktop',
    icon: path.join(__dirname, 'assets/icon.png'),
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.js')
    }
  });

  // Identify as HostelERP Desktop client in HTTP User-Agent
  const defaultUA = mainWindow.webContents.getUserAgent();
  mainWindow.webContents.setUserAgent(`${defaultUA} HostelERPDesktop/2.0 hostelerp-desktop/2.0`);

  mainWindow.loadURL(LIVE_SITE_URL);

  // Security: Handle external links (open in user's default browser)
  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    if (url.includes('google.com') || url.includes('microsoft.com') || !url.includes('azurecom')) {
      shell.openExternal(url);
      return { action: 'deny' };
    }
    return { action: 'allow' };
  });

  // Prompt "Are you sure you want to exit?" when user closes the desktop window
  mainWindow.on('close', (event) => {
    if (isQuitting) {
      return;
    }

    event.preventDefault();

    const response = dialog.showMessageBoxSync(mainWindow, {
      type: 'question',
      buttons: ['Cancel', 'Exit'],
      defaultId: 1,
      cancelId: 0,
      title: 'Exit HostelERP',
      message: 'Are you sure you want to exit?',
      detail: 'This will close the HostelERP Desktop application window.',
      icon: path.join(__dirname, 'assets/icon.png'),
      noLink: true
    });

    if (response === 1) {
      isQuitting = true;
      if (mainWindow) {
        mainWindow.destroy();
      }
      app.quit();
    }
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

// IPC listener if in-page Exit button is clicked
ipcMain.on('app-exit-request', () => {
  if (mainWindow) {
    mainWindow.close();
  }
});

function setupApplicationMenu() {
  const isMac = process.platform === 'darwin';
  const template = [
    ...(isMac ? [{
      label: 'HostelERP',
      submenu: [
        { role: 'about' },
        { type: 'separator' },
        {
          label: 'Quit HostelERP',
          accelerator: 'Command+Q',
          click: () => {
            if (mainWindow) mainWindow.close();
            else app.quit();
          }
        }
      ]
    }] : []),
    {
      label: 'File',
      submenu: [
        {
          label: 'Go Home',
          accelerator: 'CmdOrCtrl+H',
          click: () => { if (mainWindow) mainWindow.loadURL(LIVE_SITE_URL); }
        },
        {
          label: 'Print Page / Receipt',
          accelerator: 'CmdOrCtrl+P',
          click: () => { if (mainWindow) mainWindow.webContents.print(); }
        },
        { type: 'separator' },
        {
          label: 'Exit',
          accelerator: isMac ? 'Command+Q' : 'Alt+F4',
          click: () => {
            if (mainWindow) mainWindow.close();
            else app.quit();
          }
        }
      ]
    },
    {
      label: 'Edit',
      submenu: [
        { role: 'undo' },
        { role: 'redo' },
        { type: 'separator' },
        { role: 'cut' },
        { role: 'copy' },
        { role: 'paste' },
        { role: 'selectAll' }
      ]
    },
    {
      label: 'Navigate',
      submenu: [
        {
          label: 'Back',
          accelerator: isMac ? 'Cmd+Left' : 'Alt+Left',
          click: () => {
            if (mainWindow && mainWindow.webContents.canGoBack()) {
              mainWindow.webContents.goBack();
            }
          }
        },
        {
          label: 'Forward',
          accelerator: isMac ? 'Cmd+Right' : 'Alt+Right',
          click: () => {
            if (mainWindow && mainWindow.webContents.canGoForward()) {
              mainWindow.webContents.goForward();
            }
          }
        },
        { type: 'separator' },
        {
          label: 'Reload Page',
          accelerator: 'CmdOrCtrl+R',
          click: () => { if (mainWindow) mainWindow.reload(); }
        },
        {
          label: 'Force Reload',
          accelerator: 'CmdOrCtrl+Shift+R',
          click: () => { if (mainWindow) mainWindow.webContents.reloadIgnoringCache(); }
        }
      ]
    },
    {
      label: 'View',
      submenu: [
        { role: 'resetZoom' },
        { role: 'zoomIn' },
        { role: 'zoomOut' },
        { type: 'separator' },
        { role: 'togglefullscreen' }
      ]
    },
    {
      label: 'Help',
      submenu: [
        {
          label: 'Visit Official Website',
          click: () => { shell.openExternal(LIVE_SITE_URL); }
        },
        {
          label: 'GitHub Repository',
          click: () => { shell.openExternal('https://github.com/arpitchaudhary14/HostelERP'); }
        },
        { type: 'separator' },
        {
          label: 'About HostelERP',
          click: () => {
            dialog.showMessageBox(mainWindow, {
              type: 'info',
              title: 'About HostelERP',
              message: 'HostelERP Desktop v2.0 Stable',
              detail: 'Modern Hostel Management ERP System.\nDesigned & Built with modern web technologies and Electron.',
              icon: path.join(__dirname, 'assets/icon.png')
            });
          }
        }
      ]
    }
  ];

  const menu = Menu.buildFromTemplate(template);
  Menu.setApplicationMenu(menu);
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
    const urlObj = new URL(deepLinkUrl);
    const token = urlObj.searchParams.get('session_token');

    if (token && mainWindow) {
      mainWindow.loadURL(`${LIVE_SITE_URL}/auth_callback.php?session_token=${token}`);
      mainWindow.focus();
    }
  } catch (err) {
    console.error('Failed to parse deep link:', err);
  }
}

app.on('before-quit', (e) => {
  if (!isQuitting && mainWindow) {
    // Let mainWindow.on('close') trigger confirmation
    e.preventDefault();
    mainWindow.close();
  }
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
