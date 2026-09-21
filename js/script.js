(() => {
    'use strict';
    if (!document.getElementById('project-map')) return;

    const defaultProjects = [
        { id: 1, title: 'Mural para espaço cultural', category: 'Arte urbana', client: 'Espaço Vértice', location: 'Vila Madalena, São Paulo', coordinates: [-23.5566, -46.6864], budget: 'R$ 2.500 a R$ 4.000', deadline: 'Até 20 dias após a contratação', description: 'Criação e pintura de um mural de 18 m² na entrada de um espaço cultural. Buscamos uma arte colorida que represente a identidade e a diversidade do bairro.' },
        { id: 2, title: 'Fotografia para cafeteria', category: 'Fotografia', client: 'Café Aurora', location: 'Pinheiros, São Paulo', coordinates: [-23.5667, -46.6816], budget: 'R$ 800 a R$ 1.500', deadline: 'Até 7 dias após a contratação', description: 'Ensaio fotográfico do ambiente, dos cafés e dos pratos da casa. O projeto inclui 25 fotos editadas para o cardápio e as redes sociais.' },
        { id: 3, title: 'Vídeo de lançamento de coleção', category: 'Audiovisual', client: 'Ateliê Linha Livre', location: 'Consolação, São Paulo', coordinates: [-23.5526, -46.6581], budget: 'R$ 1.800 a R$ 3.000', deadline: 'Até 15 dias após a contratação', description: 'Produção de um vídeo de até 60 segundos para apresentar uma coleção independente. Inclui captação no ateliê, edição e versões verticais para redes sociais.' },
        { id: 4, title: 'Ilustração em fachada', category: 'Arte urbana', client: 'Livraria Ponto e Vírgula', location: 'Bela Vista, São Paulo', coordinates: [-23.5632, -46.6486], budget: 'R$ 1.500 a R$ 2.500', deadline: 'Até 12 dias após a contratação', description: 'Pintura artística da fachada de uma livraria de bairro, com elementos inspirados na literatura brasileira. Área aproximada de 10 m², com materiais incluídos no orçamento.' }
    ];
    const projects = Array.isArray(window.projectData) && window.projectData.length ? window.projectData : defaultProjects;
    const query = document.getElementById('project-query');
    const category = document.getElementById('project-category');
    const list = document.getElementById('project-list');
    const toggle = document.getElementById('filter-toggle');
    const filters = document.getElementById('project-filters');
    const serviceToggle = document.getElementById('project-service-toggle');
    const servicePanel = document.getElementById('project-service-panel');
    const serviceClose = document.getElementById('project-service-close');
    const markers = new Map();
    let selected = null;
    let map;
    let layer;

    if (window.L) {
        map = L.map('project-map', { zoomControl: false }).setView([-23.558, -46.668], 14);
        L.control.zoom({ position: 'topright' }).addTo(map);
        const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        tiles.on('tileerror', () => { document.getElementById('map-error').hidden = false; });
        tiles.on('tileload', () => { document.getElementById('map-error').hidden = true; });
        layer = L.layerGroup().addTo(map);
    } else {
        document.getElementById('map-error').hidden = false;
    }

    function updateSelection() {
        list.querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.id === String(selected))));
        markers.forEach((marker, id) => marker.getElement()?.classList.toggle('selected-pin', id === selected));
    }

    function isProjectOwner(project) {
        const currentUser = (window.currentUserEmail || '').trim().toLowerCase();
        const currentUserName = (window.currentUserName || '').trim().toLowerCase();
        const owner = (project.owner_email || project.ownerEmail || project.client_email || '').trim().toLowerCase();
        const client = (project.client || '').trim().toLowerCase();

        return currentUser && (owner === currentUser || (!owner && client === currentUserName));
    }

    function selectProject(project) {
        selected = String(project.id);
        ['title', 'category', 'location', 'budget', 'deadline', 'description'].forEach(key => {
            document.getElementById(`detail-${key}`).textContent = project[key];
        });
        const clientName = document.getElementById('detail-client-name');
        const clientAvatar = document.getElementById('detail-client-avatar');
        const clientEmail = project.owner_email || '';
        const clientAccount = window.accountData?.[clientEmail];
        clientName.textContent = project.client || '';
        clientAvatar.replaceChildren();
        if (clientAccount?.foto) {
            const image = document.createElement('img');
            image.src = clientAccount.foto;
            image.alt = `Foto de ${project.client || 'contratante'}`;
            clientAvatar.append(image);
        } else {
            clientAvatar.textContent = (project.client || 'CO').trim().slice(0, 2).toUpperCase();
        }
        clientAvatar.hidden = !clientEmail;
        const ownerActions = document.getElementById('project-owner-actions');
        const editId = document.getElementById('project-edit-id');
        const deleteId = document.getElementById('project-delete-id');
        const artistActions = document.getElementById('project-artist-actions');
        const applicationId = document.getElementById('project-application-id');
        const contactEmail = document.getElementById('project-contact-email');
        const isOwner = isProjectOwner(project);
        const isArtist = window.currentUserType === 'agente_criativo';
        const ownerEmail = project.owner_email || project.ownerEmail || project.client_email || '';

        if (ownerActions && editId && deleteId) {
            const projetoId = project.id || '';
            editId.value = projetoId;
            deleteId.value = projetoId;
            ownerActions.hidden = !isOwner;
        }
        if (artistActions && applicationId && contactEmail) {
            applicationId.value = project.id || '';
            contactEmail.value = ownerEmail;
            artistActions.hidden = !isArtist || !ownerEmail || isOwner;
        }

        document.getElementById('project-empty').hidden = true;
        document.getElementById('project-detail').hidden = false;
        document.getElementById('close-project').hidden = false;
        if (map) map.panTo(project.coordinates);
        updateSelection();
        if (window.matchMedia('(max-width: 760px)').matches) {
            document.querySelector('.project-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function closeProject() {
        selected = null;
        document.getElementById('project-empty').hidden = false;
        document.getElementById('project-detail').hidden = true;
        document.getElementById('close-project').hidden = true;
        updateSelection();
    }

    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    function renderProjects() {
        const search = normalize(query.value.trim());
        const visible = projects.filter(project => (!category.value || project.category === category.value)
            && normalize(`${project.title} ${project.category} ${project.location} ${project.client}`).includes(search));
        if (!visible.some(project => String(project.id) === selected)) closeProject();
        list.replaceChildren();
        if (layer) layer.clearLayers();
        markers.clear();
        document.getElementById('project-count').textContent = `${visible.length} ${visible.length === 1 ? 'projeto disponível' : 'projetos disponíveis'}`;
        if (!visible.length) {
            const empty = document.createElement('p');
            empty.className = 'detail-description';
            empty.textContent = 'Nenhum projeto encontrado. Tente outra busca ou categoria.';
            list.append(empty);
        }
        visible.forEach(project => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'project-list-item';
            button.dataset.id = String(project.id);
            const title = document.createElement('strong');
            title.textContent = project.title;
            const location = document.createElement('span');
            location.textContent = project.location;
            button.append(title, location);
            button.addEventListener('click', () => selectProject(project));
            list.append(button);
            if (map) {
                const icon = L.divIcon({ className: 'project-marker', html: '<div class="project-pin"><span>●</span></div>', iconSize: [34, 34], iconAnchor: [17, 38] });
                const marker = L.marker(project.coordinates, { icon, title: project.title, alt: project.title }).addTo(layer);
                marker.on('click', () => selectProject(project));
                markers.set(String(project.id), marker);
            }
        });
        updateSelection();
        if (map && visible.length) map.fitBounds(visible.map(project => project.coordinates), { padding: [65, 90], maxZoom: 15 });
    }

    if (serviceToggle && servicePanel) {
        serviceToggle.addEventListener('click', () => {
            const hidden = servicePanel.hidden;
            servicePanel.hidden = !hidden;
            if (!servicePanel.hidden) {
                const firstInput = servicePanel.querySelector('input, select, textarea');
                firstInput?.focus();
            }
        });
        serviceClose?.addEventListener('click', () => { servicePanel.hidden = true; });
    }
    toggle.addEventListener('click', () => {
        filters.hidden = !filters.hidden;
        toggle.setAttribute('aria-expanded', String(!filters.hidden));
        if (!filters.hidden) category.focus();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            if (!filters.hidden) {
                filters.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
            if (servicePanel && !servicePanel.hidden) {
                servicePanel.hidden = true;
            }
        }
    });
    document.querySelector('.project-search').addEventListener('submit', event => { event.preventDefault(); renderProjects(); });
    query.addEventListener('input', renderProjects);
    category.addEventListener('change', renderProjects);
    document.getElementById('close-project').addEventListener('click', closeProject);
    renderProjects();
})();
