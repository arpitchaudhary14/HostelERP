const { contextBridge, ipcRenderer } = require('electron');

// Expose safe desktop API to HostelERP web pages
contextBridge.exposeInMainWorld('hostelerpDesktop', {
  isDesktop: true,
  version: '2.0.1',
  platform: process.platform,
  requestExit: () => ipcRenderer.send('app-exit-request')
});
