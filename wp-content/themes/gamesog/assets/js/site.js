(() => {
  const root = document.documentElement;
  let saved = null; try { saved = localStorage.getItem("gamesog-theme"); } catch (_) {}
  if (saved === "light" || saved === "dark") root.dataset.theme = saved;
  const themeButtons = [...document.querySelectorAll(".theme-toggle")];
  const syncTheme = () => { const light=root.dataset.theme==="light"; themeButtons.forEach(button=>{button.setAttribute("aria-pressed",String(light));button.setAttribute("aria-label",light?"Cambiar a tema oscuro":"Cambiar a tema claro");}); };
  syncTheme();
  themeButtons.forEach(button=>button.addEventListener("click",()=>{root.dataset.theme=root.dataset.theme==="light"?"dark":"light";try{localStorage.setItem("gamesog-theme",root.dataset.theme);}catch(_){}syncTheme();}));
  const menu=document.querySelector(".menu-toggle"),nav=document.querySelector("#site-nav");
  menu?.addEventListener("click",()=>{const open=menu.getAttribute("aria-expanded")!=="true";menu.setAttribute("aria-expanded",String(open));nav?.classList.toggle("is-open",open);menu.querySelector(".screen-reader-text").textContent=open?"Cerrar menú":"Abrir menú";});
  const intro=document.querySelector("[data-intro]");
  const isHowItWorksDeepLink=window.location.hash==="#como-funciona";
  if(intro&&!isHowItWorksDeepLink){intro.hidden=false;document.body.classList.add("has-intro");root.classList.add("has-intro");const background=[document.querySelector(".site-header"),document.querySelector("body > main"),document.querySelector(".site-footer")].filter(Boolean);background.forEach(node=>node.inert=true);const start=intro.querySelector("[data-start]");start?.focus({preventScroll:true});const close=()=>{if(intro.classList.contains("is-opening"))return;intro.classList.add("is-opening");const reduced=matchMedia("(prefers-reduced-motion: reduce)").matches;setTimeout(()=>{intro.hidden=true;document.body.classList.remove("has-intro");root.classList.remove("has-intro");background.forEach(node=>node.inert=false);document.querySelector("#contenido")?.focus({preventScroll:true});},reduced?180:1320);};start?.addEventListener("click",close);}
  const summary=document.querySelector("[data-price-summary]"),designSelect=document.querySelector("[name=gamesog_design]");
  const updatePrice=()=>{if(!summary||!designSelect)return;const base=Number(summary.dataset.basePrice),fee=designSelect.value==="especial"?Number(summary.dataset.specialPrice):0;const total=summary.querySelector("[data-price-total]");if(total&&Number.isFinite(base+fee))total.textContent=new Intl.NumberFormat("es-ES",{style:"currency",currency:"EUR"}).format(base+fee);};
  designSelect?.addEventListener("change",updatePrice);
  updatePrice();
  const formatSelect=document.querySelector("[name=gamesog_format]"),formatNote=document.querySelector("[data-format-note]");
  const updateFormatNote=()=>{if(!formatSelect||!formatNote)return;formatNote.textContent=formatSelect.value==="USB"?"USB: el concepto personaliza el color y la etiqueta; no representa una carcasa de disco.":"CD y DVD: el concepto incluye una caja y carátula con el placeholder del juego.";};
  formatSelect?.addEventListener("change",updateFormatNote);
  updateFormatNote();
  const search=document.querySelector("[data-catalog-search]"),format=document.querySelector("[data-format-filter]"),design=document.querySelector("[data-design-filter]"),cards=[...document.querySelectorAll("[data-product-card]")],empty=document.querySelector("[data-empty-results]");
  const filter=()=>{let count=0;const term=(search?.value||"").trim().toLocaleLowerCase("es");cards.forEach(card=>{const designs=(card.dataset.design||"").split(/\s+/);const ok=(!term||card.dataset.name.includes(term))&&(!format?.value||card.dataset.format===format.value)&&(!design?.value||designs.includes(design.value));card.hidden=!ok;if(ok)count++;});if(empty)empty.hidden=count>0;};
  [search,format,design].forEach(el=>el?.addEventListener(el===search?"input":"change",filter));
  const motionPreference=window.matchMedia?.("(prefers-reduced-motion: reduce)");
  if("IntersectionObserver" in window&&!motionPreference?.matches){
    const revealTargets=[...document.querySelectorAll(".feature-row,.browse-card,.how-card,.team-card,[data-product-card],.woocommerce .related.products li.product")];
    try{
      const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{
        if(!entry.isIntersecting)return;
        entry.target.classList.add("is-revealed");
        observer.unobserve(entry.target);
      }),{threshold:.12,rootMargin:"0px 0px -36px 0px"});
      revealTargets.forEach((element,index)=>{
        element.classList.add("gamesog-reveal");
        element.style.setProperty("--reveal-delay",`${(index%3)*75}ms`);
        observer.observe(element);
      });
    }catch(_){
      revealTargets.forEach(element=>{element.classList.remove("gamesog-reveal");element.style.removeProperty("--reveal-delay");});
    }
  }
})();
