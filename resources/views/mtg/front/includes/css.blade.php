<style>

.panel-container {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    width: 100%;
    height: 100vh;
    padding: 20px;
    box-sizing: border-box;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    pointer-events: none; /* opcional para não interferir no AR */
    gap: 20px;
}

.panel_parent {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 20px;
}

.panel_parent .panel {
    flex: 1 1 50%;
    height: 50%;
}


.panel {
    padding: 20px;
    border-radius: 16px;
    background: rgba(100, 149, 237, 0.2); /* Azul suave (tipo cornflowerblue) */
    backdrop-filter: blur(15px) saturate(160%);
    -webkit-backdrop-filter: blur(15px) saturate(160%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.4),  /* sombra inferior leve */
        0 8px 24px rgba(0, 0, 0, 0.25); /* sombra mais profunda para relevo */
    color: #fff;
    pointer-events: auto;
    text-align: center;
    overflow-y: auto;
    margin: 10px;
}

.panel-container > .panel {
    flex: 1;
}


.panel h2 {
    margin-top: 0;
    font-size: 1.5em;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 10px;
}

.hidden-panel {
    display: none;
}

</style>