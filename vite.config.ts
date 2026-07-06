import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    manifest: true,
    outDir: 'public/assets',
    emptyOutDir: false,
    rollupOptions: {
      input: {
        app: 'resources/assets/ts/app.ts',
      },
      output: {
        entryFileNames: 'app.js',
        assetFileNames: (assetInfo) => assetInfo.name === 'app.css' ? 'app.css' : '[name][extname]',
      },
    },
  },
});

