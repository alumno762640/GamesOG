(() => {
  const palettes = {
    negro: ['#303332', '#181b1a', '#f3eedf'], crema: ['#e9dfc3', '#c7b993', '#27251f'],
    azul: ['#397ca1', '#24536e', '#f5f0e4'], coral: ['#d67d61', '#954f42', '#201e1b'],
    verde: ['#75905b', '#496841', '#f5f0e4'], burdeos: ['#813d50', '#54293e', '#f5f0e4'],
    amarillo: ['#e7bd45', '#ad842b', '#24221d'], dorado: ['#c9a54b', '#8f6b27', '#211f1a']
  };
  const defaults = {
    'red-dead-redemption-2': 'negro', minecraft: 'verde', 'cyberpunk-2077': 'amarillo',
    'elden-ring': 'dorado', 'stardew-valley': 'crema', 'hogwarts-legacy': 'burdeos'
  };
  const mediaExtensions = {
    logos: { 'red-dead-redemption-2': 'png', minecraft: 'png', 'cyberpunk-2077': 'png', 'elden-ring': 'png', 'stardew-valley': 'png', 'hogwarts-legacy': 'png' },
    fanart: { 'red-dead-redemption-2': 'jpg', minecraft: 'jpg', 'cyberpunk-2077': 'jpg', 'elden-ring': 'jpg', 'stardew-valley': 'jpg', 'hogwarts-legacy': 'jpg' },
    spines: { 'red-dead-redemption-2': 'jpg', minecraft: 'svg', 'cyberpunk-2077': 'jpg', 'elden-ring': 'jpg', 'stardew-valley': 'svg', 'hogwarts-legacy': 'jpg' }
  };
  const originalMediaExtensions = {
    covers: { 'red-dead-redemption-2': 'jpg', minecraft: 'jpg', 'cyberpunk-2077': 'jpg', 'elden-ring': 'jpg', 'stardew-valley': 'jpg', 'hogwarts-legacy': 'jpg' },
    logos: { 'red-dead-redemption-2': 'png', minecraft: 'png', 'cyberpunk-2077': 'png', 'elden-ring': 'png', 'stardew-valley': 'png', 'hogwarts-legacy': 'png' },
    fanart: { 'red-dead-redemption-2': 'jpg', minecraft: 'jpg', 'cyberpunk-2077': 'jpg', 'elden-ring': 'jpg', 'stardew-valley': 'png', 'hogwarts-legacy': 'png' },
    spines: { 'red-dead-redemption-2': 'jpg', minecraft: 'svg', 'cyberpunk-2077': 'jpg', 'elden-ring': 'jpg', 'stardew-valley': 'svg', 'hogwarts-legacy': 'jpg' }
  };
  const specialPalettes = {
    'cyberpunk-2077': ['#e8c83e', '#17191a', '#f6e5a5'],
    'elden-ring': ['#bd9b58', '#171613', '#f0d997'],
    'hogwarts-legacy': ['#73344a', '#17171a', '#d9bd82']
  };
  const esc = value => String(value || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&apos;' }[c]));
  const fallbackArt = (slug, ink, accent) => {
    if (slug.includes('minecraft')) return '<path d="M410 430h180v-90h180v90h-90v80H500v-80h-90Z" fill="' + ink + '"/><rect x="580" y="250" width="90" height="90" fill="' + accent + '"/>';
    if (slug.includes('cyberpunk')) return '<path d="M500 500V330h65v170m20 0V250h85v250m22 0V350h65v150" fill="' + ink + '"/><path d="M520 360h20v30h-20m130-90h20v40h-20m70 90h20v25h-20" fill="' + accent + '"/>';
    if (slug.includes('elden')) return '<circle cx="640" cy="380" r="125" fill="none" stroke="' + accent + '" stroke-width="14"/><path d="M640 245v270m0-155-80-55m80 145 85-70" fill="none" stroke="' + accent + '" stroke-width="12"/>';
    if (slug.includes('stardew')) return '<circle cx="700" cy="330" r="58" fill="' + accent + '"/><path d="M420 490q110-145 220-35 95-95 190 35Z" fill="' + ink + '"/>';
    if (slug.includes('hogwarts')) return '<path d="M480 500V370l50 35 40-130 42 90 50-58 50 193Z" fill="' + ink + '"/><path d="M630 290v-74m-25 74h50" stroke="' + accent + '" stroke-width="9"/><circle cx="745" cy="315" r="42" fill="' + accent + '"/>';
    return '<path d="M420 500 555 375l90 82 105-118 110 161Z" fill="' + ink + '"/><circle cx="730" cy="315" r="65" fill="' + accent + '"/>';
  };
  const specialEngraving = (slug, x, y, w, h, color) => {
    if (slug.includes("elden")) return '<g fill="none" stroke="' + color + '" stroke-opacity=".38" stroke-width="3"><circle cx="' + (x+w/2) + '" cy="' + (y+h/2) + '" r="' + Math.min(w,h)*.38 + '"/><circle cx="' + (x+w/2) + '" cy="' + (y+h/2) + '" r="' + Math.min(w,h)*.31 + '"/><path d="M' + (x+35) + ' ' + (y+55) + 'h42m-21-21v42M' + (x+w-77) + ' ' + (y+h-55) + 'h42m-21-21v42"/></g>';
    if (slug.includes("cyberpunk")) return '<g fill="none" stroke="' + color + '" stroke-opacity=".4" stroke-width="4"><path d="M' + (x+20) + ' ' + (y+100) + 'h80l36 36h45m' + (w-210) + ' 0h30v-75h55M' + (x+25) + ' ' + (y+h-95) + 'h70l35-35h55m' + (w-195) + ' 0h54v54h40"/><circle cx="' + (x+100) + '" cy="' + (y+100) + '" r="8" fill="' + color + '"/><circle cx="' + (x+w-65) + '" cy="' + (y+h-42) + '" r="8" fill="' + color + '"/></g>';
    return '<g fill="none" stroke="' + color + '" stroke-opacity=".42" stroke-width="4"><path d="M' + (x+30) + ' ' + (y+135) + 'c35-95 95-95 115-38 18 52-48 74-65 37-12-28 20-45 38-25M' + (x+w-30) + ' ' + (y+h-135) + 'c-35 95-95 95-115 38-18-52 48-74 65-37 12 28-20 45-38 25"/><path d="M' + (x+25) + ' ' + (y+35) + 'l28 28m-28 0 28-28M' + (x+w-55) + ' ' + (y+h-55) + 'l28 28m-28 0 28-28"/></g>';
  };
  const artImage = (img, x, y, w, h, radius, fallback, fit = "meet", imageData = img.dataset.coverData) => {
    const clipId = "art-clip-" + x + "-" + y;
    const shape = '<clipPath id="' + clipId + '"><rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '" rx="' + radius + '"/></clipPath>';
    const image = imageData
      ? '<image href="' + imageData + '" x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '" preserveAspectRatio="xMidYMid ' + fit + '" clip-path="url(#' + clipId + ')"/>'
      : '<g>' + fallback + '</g>';
    return '<defs>' + shape + '</defs><rect x="' + x + '" y="' + y + '" width="' + w + '" height="' + h + '" rx="' + radius + '" fill="#262826"/>' + image;
  };
  const disc = (cx, cy, accent) => '<g filter="url(#shadow)"><circle cx="' + cx + '" cy="' + cy + '" r="91" fill="url(#disc-metal)" stroke="#d8d4c9" stroke-width="5"/><circle cx="' + cx + '" cy="' + cy + '" r="69" fill="none" stroke="' + accent + '" stroke-opacity=".7" stroke-width="3"/><circle cx="' + cx + '" cy="' + cy + '" r="14" fill="#282b2a" stroke="#ece8dc" stroke-width="5"/><path d="M' + (cx-65) + ' ' + (cy-45) + 'q60-40 118 0" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="5"/></g>';
  const packageMockup = (img, title, format, special, palette) => {
    const x = 300, y = 142, w = 450, h = 675, spineW = 48, spineX = x - spineW;
    const accent = palette[0];
    const spinePath = 'M' + spineX + ' ' + y + ' L' + x + ' ' + y + ' L' + x + ' ' + (y + h) + ' L' + spineX + ' ' + (y + h) + ' Z';
    const spineFill = special ? '#383a38' : '#252827';
    const frontArt = special ? (img.dataset.fanartData || img.dataset.coverData) : img.dataset.coverData;
    const shell = '<rect x="' + (x - 3) + '" y="' + (y - 3) + '" width="' + (w + 6) + '" height="' + (h + 6) + '" rx="4" fill="' + (special ? 'url(#steel-edge)' : '#262a28') + '" stroke="' + (special ? '#c9c7bd' : '#777a74') + '" stroke-width="2"/>';
    const art = artImage(img, x, y, w, h, 0, fallbackArt(img.dataset.gamesogSlug, palette[1], accent), 'slice', frontArt);
    const spineClipId = 'spine-clip-' + String(img.dataset.gamesogSlug || 'game').replace(/[^a-z0-9-]/gi, '');
    const spineBase = '<path d="' + spinePath + '" fill="' + spineFill + '" stroke="#777a74" stroke-width="2"/>';
    const spine = img.dataset.spineData && (!special || img.dataset.gamesogCatalog === '1')
      ? '<defs><clipPath id="' + spineClipId + '"><path d="' + spinePath + '"/></clipPath></defs><image href="' + img.dataset.spineData + '" x="' + spineX + '" y="' + y + '" width="' + spineW + '" height="' + h + '" preserveAspectRatio="xMidYMid slice" clip-path="url(#' + spineClipId + ')"/><path d="' + spinePath + '" fill="none" stroke="#777a74" stroke-width="2"/>'
      : spineBase + '<text transform="translate(' + (spineX + 32) + ' ' + (y + h - 22) + ') rotate(-90)" fill="#f4eee0" font-family="Arial,sans-serif" font-size="15" font-weight="700" letter-spacing="2">' + esc(title.toUpperCase()) + '</text>';
    const sheen = special ? '<path d="M' + (x + 3) + ' ' + (y + 2) + 'v' + (h - 4) + '" stroke="url(#shine)" stroke-width="2" opacity=".45"/>' : '';
    return '<g filter="url(#shadow)">' + spine + shell + art + sheen + '</g><text x="525" y="880" text-anchor="middle" fill="#c9c5ba" font-family="Arial,sans-serif" font-size="16" letter-spacing="3">' + (special ? 'STEELBOOK · EDICIÓN ESPECIAL' : 'CAJA ' + esc(format) + ' · PORTADA Y LOMO') + '</text>';
  };  const usbMockup = (img, title, special, palette) => {
    const accent = palette[0], edge = special ? palette[0] : palette[1];
    const illustration = special
      ? artImage(img, 342, 292, 428, 127, 26, fallbackArt(img.dataset.gamesogSlug, palette[1], accent), 'slice', img.dataset.fanartData || img.dataset.coverData)
      : (img.dataset.logoData ? '<image href="' + img.dataset.logoData + '" x="382" y="304" width="350" height="92" preserveAspectRatio="xMidYMid meet"/><text x="555" y="411" text-anchor="middle" fill="' + palette[2] + '" font-family="Arial,sans-serif" font-size="10" font-weight="700" letter-spacing="1">' + esc(title.toUpperCase()) + '</text>' : '<text x="555" y="370" text-anchor="middle" fill="' + palette[2] + '" font-family="Arial,sans-serif" font-size="30" font-weight="700" letter-spacing="1">' + esc(title) + '</text>');
    return '<g transform="translate(-310 -344) scale(1.65) rotate(-5 530 355)" filter="url(#shadow)"><rect x="234" y="318" width="128" height="74" rx="7" fill="url(#usb-metal)" stroke="#babbb4" stroke-width="4"/><rect x="253" y="332" width="14" height="18" rx="2" fill="#767a76"/><rect x="253" y="360" width="14" height="18" rx="2" fill="#767a76"/><rect x="334" y="284" width="444" height="143" rx="31" fill="url(#usb-body)" stroke="' + edge + '" stroke-width="7"/>' + illustration + '<circle cx="744" cy="354" r="9" fill="' + (special ? accent : palette[2]) + '" fill-opacity=".85"/></g><text x="505" y="515" text-anchor="middle" fill="#c9c5ba" font-family="Arial,sans-serif" font-size="16" letter-spacing="3">' + (special ? 'ILUSTRACIÓN FIJA · EDICIÓN ESPECIAL' : 'ACABADO ' + esc((img.dataset.gamesogColor || 'negro').toUpperCase())) + '</text>';
  };  const init = () => {
    document.querySelectorAll('.product-card[data-product-card]').forEach(card => {
      const img = card.querySelector('img'); if (!img) return;
      const name = card.querySelector('h3')?.textContent || '';
      const slug = (card.querySelector('a')?.href.split('/').filter(Boolean).pop() || '').replace(/[^a-z0-9-]/g, '');
      const src = img.getAttribute('src') || '';
      const cover = card.dataset.coverUrl || (src.slice(0, src.lastIndexOf('/games/')) + '/covers/' + slug + '.jpg');
      Object.assign(img.dataset, { gamesogMockup: '1', gamesogTitle: name, gamesogSlug: slug, gamesogCover: cover, gamesogFormat: card.dataset.format || 'DVD', gamesogDesign: card.dataset.defaultDesign || 'basico', gamesogColor: defaults[slug] || 'negro' });
    });
    const form = document.querySelector('.gamesog-options');
    const fields = { format: form?.querySelector('[name="gamesog_format"]'), design: form?.querySelector('[name="gamesog_design"]'), color: form?.querySelector('select[name="gamesog_color"]'), fixedColor: form?.querySelector('input[type="hidden"][name="gamesog_color"]') };
    const draw = img => {
      if (img.classList.contains("gamesog-mockup-loading")) return;
      const isMain = !!(form && img.closest('.gamesog-product-gallery'));
      const format = isMain ? (fields.format?.value || img.dataset.gamesogFormat || 'DVD') : (img.dataset.gamesogFormat || 'DVD');
      const design = isMain ? (fields.design?.value || 'basico') : (img.dataset.gamesogDesign || 'basico');
      const special = design === 'especial';
      img.dataset.gamesogFormat = format;
      const slug = img.dataset.gamesogSlug || '';
      const title = img.dataset.gamesogTitle || 'Edición de muestra';
      const palette = special ? (specialPalettes[slug] || specialPalettes['cyberpunk-2077']) : (palettes[isMain ? (fields.color?.value || defaults[slug] || 'negro') : (img.dataset.gamesogColor || defaults[slug] || 'negro')] || palettes.negro);
      const accent = palette[0];
      const object = format === 'USB' ? usbMockup(img, title, special, palette) : packageMockup(img, title, format, special, palette);
      const isUsb = format === 'USB';
      const viewBox = isUsb ? '0 70 1000 520' : '205 95 590 830';
      const glowY = isUsb ? 320 : 470;
      const glowRx = isUsb ? 450 : 270;
      const glowRy = isUsb ? 220 : 360;
      const svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' + viewBox + '"><defs><linearGradient id="bg" x2="0" y2="1"><stop stop-color="#2a2c2a"/><stop offset="1" stop-color="#191b1a"/></linearGradient><radialGradient id="glow" gradientUnits="userSpaceOnUse" cx="510" cy="' + glowY + '" r="1" gradientTransform="translate(510 ' + glowY + ') scale(' + glowRx + ' ' + glowRy + ') translate(-510 -' + glowY + ')"><stop stop-color="' + accent + '" stop-opacity=".66"/><stop offset=".52" stop-color="' + accent + '" stop-opacity=".34"/><stop offset="1" stop-color="' + accent + '" stop-opacity="0"/></radialGradient><linearGradient id="disc-metal" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#f4f1e8"/><stop offset=".3" stop-color="#a7aaa5"/><stop offset=".55" stop-color="#e2dfd6"/><stop offset=".8" stop-color="#888b87"/><stop offset="1" stop-color="#f6f3eb"/></linearGradient><linearGradient id="steel-edge" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#f8f7ef"/><stop offset=".22" stop-color="#777b78"/><stop offset=".48" stop-color="#ebe8de"/><stop offset=".75" stop-color="#555956"/><stop offset="1" stop-color="#d6d3ca"/></linearGradient><linearGradient id="shine"><stop stop-color="#fff" stop-opacity=".08"/><stop offset=".48" stop-color="#fff" stop-opacity=".7"/><stop offset="1" stop-color="#fff" stop-opacity=".03"/></linearGradient><linearGradient id="usb-metal" x2="0" y2="1"><stop stop-color="#faf8ef"/><stop offset=".45" stop-color="#a4a7a2"/><stop offset="1" stop-color="#626763"/></linearGradient><linearGradient id="usb-body" x2="0" y2="1"><stop stop-color="' + (special ? '#444846' : palette[0]) + '"/><stop offset=".48" stop-color="' + (special ? '#262a29' : palette[0]) + '"/><stop offset="1" stop-color="' + (special ? '#151817' : palette[1]) + '"/></linearGradient><filter id="shadow" x="-30%" y="-30%" width="170%" height="180%"><feDropShadow dx="0" dy="20" stdDeviation="16" flood-color="#000" flood-opacity=".55"/></filter></defs><ellipse cx="510" cy="' + glowY + '" rx="' + glowRx + '" ry="' + glowRy + '" fill="url(#glow)"/>' + object + '</svg>';
      img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
      img.alt = (format === 'USB' ? 'Maqueta de memoria USB' : (format === 'CD' ? 'Maqueta de caja de CD' : 'Maqueta de caja DVD')) + ' para ' + title + (special ? ', edición especial con arte fijo' : ', diseño básico' + (format === 'USB' ? ' en color ' + (fields.color?.value || img.dataset.gamesogColor || 'negro') : ' con carátula frontal y lomo lateral'));
      img.classList.add('gamesog-generated-mockup');
    };
    const syncChoice = () => {
      if (!form) return;
      const special = fields.design?.value === 'especial';
      const colorLabel = form.querySelector('.gamesog-color-choice');
      if (colorLabel) colorLabel.hidden = special;
      if (fields.color) { fields.color.disabled = special; fields.color.required = !special; }
      if (fields.fixedColor) { fields.fixedColor.disabled = !special; }
      document.querySelectorAll('[data-gamesog-mockup]').forEach(draw);
      const note = form.querySelector('[data-format-note]');
      if (note) {
        const usb = fields.format?.value === 'USB';
        note.textContent = usb ? (special ? 'USB especial: ilustración fija impresa en el dispositivo; no admite selección de color.' : 'USB básico: cuerpo en el color elegido y nombre del juego grabado.') : (special ? 'CD y DVD especiales: caja metálica tipo SteelBook con arte fijo.' : 'CD y DVD básicos: caja física con carátula frontal y lomo lateral.');
      }
    };
    const images = [...document.querySelectorAll('[data-gamesog-mockup]')];
    const blobAsDataUrl = blob => new Promise(resolve => {
      const reader = new FileReader();
      reader.onload = () => resolve(reader.result);
      reader.onerror = () => resolve(null);
      reader.readAsDataURL(blob);
    });
    images.forEach(img => {
      img.classList.add('gamesog-mockup-loading');
      const slug = img.dataset.gamesogSlug || '';
      const coverUrl = img.dataset.gamesogCover || '';
      const markerAt = coverUrl.lastIndexOf('/covers/');
      const rootBase = markerAt < 0 ? '' : coverUrl.slice(0, markerAt + 1);
      const root = !rootBase ? "" : (rootBase.endsWith("/optimized/") ? rootBase : rootBase + "optimized/");
      const isMain = !!(form && img.closest('.gamesog-product-gallery'));
      const format = isMain ? (fields.format?.value || img.dataset.gamesogFormat || 'DVD') : (img.dataset.gamesogFormat || 'DVD');
      const design = isMain ? (fields.design?.value || 'basico') : (img.dataset.gamesogDesign || 'basico');
      const sources = (format === 'USB' && !isMain) ? [] : [['coverData', 'covers', 'jpg']];
      if (isMain) {
        sources.push(['spineData', 'spines', mediaExtensions.spines[slug]], ['logoData', 'logos', mediaExtensions.logos[slug]], ['fanartData', 'fanart', mediaExtensions.fanart[slug]]);
      } else if (format === 'USB') {
        sources.push(design === 'especial' ? ['fanartData', 'fanart', mediaExtensions.fanart[slug]] : ['logoData', 'logos', mediaExtensions.logos[slug]]);
      } else {
        if (design !== 'especial' || img.dataset.gamesogCatalog === '1') sources.push(['spineData', 'spines', mediaExtensions.spines[slug]]);
        if (design === 'especial') sources.push(['fanartData', 'fanart', mediaExtensions.fanart[slug]]);
      }
      const load = async () => {
        if (root) {
          await Promise.allSettled(sources.map(async ([key, folder, extension]) => {
            if (!extension) return;
            const originalRoot = root.replace(/optimized\/$/, '');
            const assetRoot = key === 'spineData' ? originalRoot : root;
            const version = key === 'spineData' && img.dataset.gamesogMediaVersion ? '?v=' + encodeURIComponent(img.dataset.gamesogMediaVersion) : '';
            const url = key === 'coverData' ? coverUrl : assetRoot + folder + '/' + slug + '.' + extension + version;
            let response = await fetch(url, { credentials: 'same-origin', cache: 'reload' });
            let blob = response.ok ? await response.blob() : null;
            if (!blob || !blob.type.startsWith('image/')) {
              const extension = originalMediaExtensions[folder]?.[slug];
              if (!extension) return;
              response = await fetch(originalRoot + folder + '/' + slug + '.' + extension + version, { credentials: 'same-origin', cache: 'reload' });
              if (!response.ok) return;
              blob = await response.blob();
            }
            if (!blob.type.startsWith('image/')) return;
            const data = await blobAsDataUrl(blob);
            if (data) {
              img.dataset[key] = data;
              if (key === 'coverData' || img.dataset.coverData) {
                img.classList.remove('gamesog-mockup-loading');
                draw(img);
                img.classList.add('gamesog-mockup-ready');
              }
            }
          }));
        }
        img.classList.remove('gamesog-mockup-loading');
        draw(img);
        if (img.decode) { try { await img.decode(); } catch (_) {} }
        img.classList.add('gamesog-mockup-ready');
      };
      const enhance = () => load().catch(() => {
        img.classList.remove('gamesog-mockup-loading');
        draw(img);
        img.classList.add('gamesog-mockup-ready');
      });
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
          if (entries.some(entry => entry.isIntersecting)) { observer.disconnect(); enhance(); }
        }, { rootMargin: '220px 0px' });
        observer.observe(img);
      } else { enhance(); }
    });    fields.design?.addEventListener('change', syncChoice);
    fields.color?.addEventListener('change', syncChoice);
    fields.format?.addEventListener('change', syncChoice);
    syncChoice();
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true }); else init();
})();
