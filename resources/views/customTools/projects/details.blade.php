@extends('layouts.app')

@section('content')

<style>
body { font-family: Arial, sans-serif; background: #f4f4f9; }
.tabs { display: flex; border-bottom: 2px solid #ccc; margin-bottom: 20px; }
.tabs button { background: none; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; }
.tabs button.active { border-bottom: 3px solid #007BFF; font-weight: bold; }
.tab-content { display: none; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.tab-content.active { display: block; }
.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: bold; }
input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
.color-list { display: flex; gap: 10px; }
.color-list input { width: 60px; height: 40px; padding: 0; cursor: pointer; }
.accordion-body{ background-color: #FFF; }
</style>

    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel" style="text-align: center;"> <h3 style="margin-bottom: 0;">PROJECT DETAILS</h3> </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="accordion" id="projectAccordion">
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingProject">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProject" aria-expanded="true" aria-controls="collapseProject"> PROJECT DETAILS </button>
                </h2>
                <div id="collapseProject" class="accordion-collapse collapse show" aria-labelledby="headingProject" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">


                    <div class="row">
                        <div class="col-lg-1 col-md-12 mb-3">
                            <img style="width: 140px;margin: auto;border-radius: 5px;padding: 5px;border: 1px solid #999;" src="https://webtools-manager.com/images/logos/logo vertical.png">
                        </div>
                        <div class="col-lg-1 col-md-12 mb-3">
                            <div class="social-links" style="text-align: center;display: grid;">
                              <a href="https://www.facebook.com/tua_pagina" target="_blank" style="margin: 5px;">
                                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="Facebook" width="25" height="25">
                              </a>
                              <br>
                              <a href="https://www.instagram.com/tua_pagina" target="_blank" style="margin: 5px;">
                                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/instagram.svg" alt="Instagram" width="25" height="25">
                              </a>
                              <br>
                              <a href="https://www.linkedin.com/in/tua_pagina" target="_blank" style="margin: 5px;">
                                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/linkedin.svg" alt="LinkedIn" width="25" height="25">
                              </a>
                              <br>
                              <a href="https://x.com/tua_pagina" target="_blank" style="margin: 5px;">
                                <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/x.svg" alt="X (Twitter)" width="25" height="25">
                              </a>
                            </div>

                        </div>
                        <div class="col-lg-6 col-md-12 mb-3">
                          <label for="formGroupExampleInput" class="form-label">Project description</label>
                          <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
                        </div>

                      <!-- Fonte -->

                    
                      <!-- Slogan -->
                        <div class="col-lg-2 col-md-12 mb-3">
                      <div class="mb-4 copy-item" data-copy="Casual Looks, Cool Vibes">
                        <h6>Slogan</h6>
                        <blockquote class="blockquote">"Casual Looks, Cool Vibes"</blockquote>
                      </div>
                      </div>
                      
                        <div class="col-lg-2 col-md-12 mb-3">
                            <div>
                          <label for="formGroupExampleInput" class="form-label">Target audience</label>
                          <p>Gamer; Collector; Fashion</p>
                          </div>
                            <div>
                          <label for="formGroupExampleInput" class="form-label">Product description</label>
                          <p>Gamer; Collector; Fashion</p>
                          </div>
                        </div>
                    </div>


                  </div>
                </div>
              </div>
            
              <!-- THEME AND DESIGN -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingDesign">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDesign" aria-expanded="false" aria-controls="collapseDesign">
                    THEME AND DESIGN
                  </button>
                </h2>
                <div id="collapseDesign" class="accordion-collapse collapse" aria-labelledby="headingDesign" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">
                    
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-12">
                                <h6>Paleta de Cores Principais</h6>
                                <div class="d-flex gap-2">
                                    <div class="p-3 rounded copy-item" style="background:#FFD700;" data-copy="#FFD700" title="Clica para copiar"></div>
                                    <div class="p-3 rounded copy-item" style="background:#000000;" data-copy="#000000" title="Clica para copiar"></div>
                                    <div class="p-3 rounded copy-item" style="background:#FFFFFF;border:1px solid #ccc;" data-copy="#FFFFFF" title="Clica para copiar"></div>
                                    <div class="p-3 rounded copy-item" style="background:#1E90FF;" data-copy="#1E90FF" title="Clica para copiar"></div>
                                    <div class="p-3 rounded copy-item" style="background:#FF4500;" data-copy="#FF4500" title="Clica para copiar"></div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-12">
                                <div class="mb-4 copy-item" data-copy="Roboto, sans-serif">
                                    <h6>Fonte Principal</h6>
                                    <p>Roboto, sans-serif</p>
                                </div>
                            </div>
                      
                            <div class="col-lg-3 col-md-12 copy-item" data-copy="ExemploTheme">
                                <h6>Nome do Tema</h6>
                                <p><strong>ExemploTheme</strong></p>
                            </div>
                            <div class="col-lg-3 col-md-12 copy-item" data-copy="PrestaShop">
                                <h6>Plataforma Base</h6>
                                <p>PrestaShop</p>
                            </div>
                        </div>
                    
                      <!-- Linha Gráfica -->
                      <div class="mb-4 copy-item" data-copy="A linha gráfica aposta em tons claros e modernos, com destaque para minimalismo, legibilidade e contraste visual.">

                        <div class="row g-3">
                            <div class="col-lg-12 text-center" style="background-color: #eee; border: 1px solid #ddd;border-radius: 5px;padding: 5px;"> 
                                <h4>Linha Gráfica</h4>
                            </div>
                            <div class="col-lg-12 text-center"> 
                                <p>A linha gráfica aposta em tons claros e modernos, com destaque para minimalismo, legibilidade e contraste visual. Os elementos visuais acompanham o posicionamento jovem e descontraído da marca.</p>
                            </div>
                        </div>
                      </div>
                      <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-lg-12 text-center" style="background-color: #eee; border: 1px solid #ddd;border-radius: 5px;padding: 5px;"> 
                                <h4>Variações dos Logos</h4>
                            </div>
                          <div class="col-md-3 col-6 copy-item" data-copy="logo-principal.png" style="cursor: pointer;text-align: center;">
                                <img style="width: 140px;margin: 0 auto;border-radius: 5px;padding: 5px;border: 1px solid #999;" src="https://webtools-manager.com/images/logos/logo vertical.png">
                                <p class="text-center mt-2 small">Principal</p>
                          </div>
                          <div class="col-md-3 col-6 copy-item" data-copy="logo-secundario.png" style="cursor: pointer;text-align: center;">
                                <img style="width: 140px;margin: 0 auto;border-radius: 5px;padding: 5px;border: 1px solid #999;" src="https://webtools-manager.com/images/logos/logo vertical.png">
                                <p class="text-center mt-2 small">Secundário</p>
                          </div>
                          <div class="col-md-3 col-6 copy-item" data-copy="logo-monocromatico.png" style="cursor: pointer;text-align: center;">
                                <img style="width: 140px;margin: 0 auto;border-radius: 5px;padding: 5px;border: 1px solid #999;" src="https://webtools-manager.com/images/logos/logo vertical.png">
                                <p class="text-center mt-2 small">Monocromático</p>
                          </div>
                          <div class="col-md-3 col-6 copy-item" data-copy="logo-simplificado.png" style="cursor: pointer;text-align: center;">
                                <img style="width: 140px;margin: 0 auto;border-radius: 5px;padding: 5px;border: 1px solid #999;" src="https://webtools-manager.com/images/logos/logo vertical.png">
                                <p class="text-center mt-2 small">Simplificado</p>
                          </div>
                        </div>
                      </div>
                    
                    <!-- Script para copiar ao clique -->
                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                      const items = document.querySelectorAll(".copy-item");
                      items.forEach(el => {
                        el.style.cursor = "pointer";
                        el.addEventListener("click", () => {
                          const text = el.getAttribute("data-copy");
                          navigator.clipboard.writeText(text).then(() => {
                            // Alerta rápido (podes trocar por toast Bootstrap)
                            const oldTitle = el.getAttribute("title") || "";
                            el.setAttribute("title", "Copiado! ✅");
                            setTimeout(() => el.setAttribute("title", oldTitle), 1500);
                          });
                        });
                      });
                    });
                    </script>


                  </div>
                </div>
              </div>
            
              <!-- BRAND -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingBrand">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBrand" aria-expanded="false" aria-controls="collapseBrand">
                    BRAND
                  </button>
                </h2>
                <div id="collapseBrand" class="accordion-collapse collapse" aria-labelledby="headingBrand" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">
                    Conteúdo do Brand
                  </div>
                </div>
              </div>
            
              <!-- CONTACTS -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingContacts">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContacts" aria-expanded="false" aria-controls="collapseContacts">
                    CONTACTS
                  </button>
                </h2>
                <div id="collapseContacts" class="accordion-collapse collapse" aria-labelledby="headingContacts" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">
                    Conteúdo do Contacts
                  </div>
                </div>
              </div>
            
              <!-- SOCIAL MEDIA -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingSocialMedia">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocialMedia" aria-expanded="false" aria-controls="collapseSocialMedia">
                    SOCIAL MEDIA
                  </button>
                </h2>
                <div id="collapseSocialMedia" class="accordion-collapse collapse" aria-labelledby="headingSocialMedia" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">
                    Conteúdo do Social Media
                  </div>
                </div>
              </div>
            
              <!-- STRUCTURE -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingStructure">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStructure" aria-expanded="false" aria-controls="collapseStructure">
                    STRUCTURE
                  </button>
                </h2>
                <div id="collapseStructure" class="accordion-collapse collapse" aria-labelledby="headingStructure" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">
                    Conteúdo do Structure
                  </div>
                </div>
              </div>
            
              <!-- TEAM -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingTeam">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeam" aria-expanded="false" aria-controls="collapseTeam">
                    TEAM
                  </button>
                </h2>
                <div id="collapseTeam" class="accordion-collapse collapse" aria-labelledby="headingTeam" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">
                    Conteúdo do Team
                  </div>
                </div>
              </div>
            
              <!-- DOCS -->
              <div class="accordion-item">
                <h2 class="accordion-header" id="headingDocs">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocs" aria-expanded="false" aria-controls="collapseDocs">
                    DOCS
                  </button>
                </h2>
                <div id="collapseDocs" class="accordion-collapse collapse" aria-labelledby="headingDocs" data-bs-parent="#projectAccordion">
                  <div class="accordion-body">
                    Conteúdo do Docs
                  </div>
                </div>
              </div>
            
            </div>
            
        </div>
    </div>
    
<script>
const tabs = document.querySelectorAll('.tab-link');
const contents = document.querySelectorAll('.tab-content');


tabs.forEach(tab => {
tab.addEventListener('click', () => {
tabs.forEach(t => t.classList.remove('active'));
contents.forEach(c => c.classList.remove('active'));


tab.classList.add('active');
document.getElementById(tab.dataset.tab).classList.add('active');
});
});
</script>

@endsection