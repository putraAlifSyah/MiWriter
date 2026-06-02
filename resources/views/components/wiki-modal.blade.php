<!-- Wiki Entity Modal -->
<style>
.nwp-wiki-modal {
    background: var(--color-bg);
    border-radius: var(--radius-lg);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    border: 1px solid var(--color-border);
    max-width: 450px;
    width: 90%;
    /* removed overflow:hidden to allow avatar to overlap properly */
    transform: scale(0.95);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.nwp-modal-overlay--active .nwp-wiki-modal {
    transform: scale(1);
    opacity: 1;
}
.nwp-wiki-scroll::-webkit-scrollbar {
    width: 6px;
}
.nwp-wiki-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.nwp-wiki-scroll::-webkit-scrollbar-thumb {
    background-color: var(--color-border);
    border-radius: 10px;
}
</style>

<div id="wiki-modal" class="nwp-modal-overlay" style="z-index:90000; align-items:center; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.7);">
    <div class="nwp-wiki-modal" style="position:relative; display:flex; flex-direction:column; max-height:85vh;">
        <!-- Banner Header -->
        <div id="wiki-modal-header" style="height:100px; border-top-left-radius:var(--radius-lg); border-top-right-radius:var(--radius-lg); background:linear-gradient(135deg, var(--color-bg-secondary), var(--color-border)); position:relative; flex-shrink:0;">
            <button type="button" onclick="WikiModal.close()" style="position:absolute; top:16px; right:16px; background:rgba(0,0,0,0.3); backdrop-filter:blur(4px); border:1px solid rgba(255,255,255,0.1); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:18px; color:#fff; z-index:10; transition:background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.6)'" onmouseout="this.style.background='rgba(0,0,0,0.3)'">&times;</button>
        </div>
        
        <!-- Profile Content -->
        <div style="padding:0 24px 24px; position:relative; display:flex; flex-direction:column; flex:1; overflow:hidden; border-bottom-left-radius:var(--radius-lg); border-bottom-right-radius:var(--radius-lg);">
            <!-- Avatar -->
            <div style="display:flex; justify-content:center; margin-top:-48px; position:relative; z-index:5; margin-bottom:16px;">
                <img id="wiki-modal-image" src="" alt="" style="width:96px; height:96px; border-radius:50%; border:4px solid var(--color-bg); object-fit:cover; display:none; background:var(--color-bg-secondary); box-shadow:0 4px 12px rgba(0,0,0,0.2);">
                <div id="wiki-modal-image-placeholder" style="width:96px; height:96px; border-radius:50%; border:4px solid var(--color-bg); display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg, var(--color-bg-secondary), var(--color-border)); font-size:36px; font-weight:bold; color:var(--color-text-primary); box-shadow:0 4px 12px rgba(0,0,0,0.2);">?</div>
            </div>
            
            <!-- Title & Badges -->
            <div style="text-align:center; margin-bottom:24px; flex-shrink:0;">
                <h3 id="wiki-modal-name" class="nwp-heading" style="margin:0 0 8px 0; font-size:24px; font-weight:700; letter-spacing:-0.02em;">Name</h3>
                <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                    <span id="wiki-modal-type" style="padding:4px 10px; border-radius:99px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:var(--color-accent);">TYPE</span>
                    <span id="wiki-modal-desc" style="padding:4px 10px; border-radius:99px; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; background:rgba(255,255,255,0.05); color:var(--color-text-muted);">Desc</span>
                </div>
            </div>
            
            <!-- Details Scroll Area -->
            <div id="wiki-modal-details" class="nwp-wiki-scroll" style="flex:1; overflow-y:auto; padding-right:12px; display:flex; flex-direction:column; gap:20px;">
                <!-- Details injected here -->
            </div>
        </div>
    </div>
</div>

<script>
const WikiModal = {
    modal: null,
    
    init() {
        this.modal = document.getElementById('wiki-modal');
    },
    
    open(entityJson) {
        if (!this.modal) this.init();
        
        try {
            const data = typeof entityJson === 'string' ? JSON.parse(entityJson) : entityJson;
            
            document.getElementById('wiki-modal-name').textContent = data.name || 'Unknown';
            document.getElementById('wiki-modal-type').textContent = data.type || 'Entity';
            
            const descEl = document.getElementById('wiki-modal-desc');
            if (data.desc) {
                descEl.textContent = data.desc;
                descEl.style.display = 'inline-block';
            } else {
                descEl.style.display = 'none';
            }
            
            // Theming based on color or type
            let themeColor = data.color ? data.color.hex : 'var(--color-accent)';
            let themeGradient = data.color ? `linear-gradient(135deg, ${themeColor}40, var(--color-bg-secondary))` : 'linear-gradient(135deg, var(--color-accent)40, var(--color-bg-secondary))';
            
            document.getElementById('wiki-modal-type').style.color = themeColor;
            document.getElementById('wiki-modal-header').style.background = themeGradient;
            
            // Image
            const imgEl = document.getElementById('wiki-modal-image');
            const phEl = document.getElementById('wiki-modal-image-placeholder');
            if (data.details && data.details.Image) {
                imgEl.src = data.details.Image;
                imgEl.style.display = 'block';
                phEl.style.display = 'none';
            } else {
                imgEl.src = '';
                imgEl.style.display = 'none';
                phEl.style.display = 'flex';
                phEl.textContent = data.name ? data.name.charAt(0).toUpperCase() : '?';
                phEl.style.background = themeGradient;
            }
            
            // Details
            const detailsContainer = document.getElementById('wiki-modal-details');
            detailsContainer.innerHTML = '';
            
            if (data.details) {
                // Ensure 'Notes' is renamed to 'Recent Developments' for better UX
                const orderedKeys = ['Physical Description', 'Personality Traits', 'Motivations', 'Backstory', 'Notes'];
                
                for (const key of orderedKeys) {
                    const val = data.details[key];
                    if (!val) continue;
                    
                    const displayKey = key === 'Notes' ? 'Recent Developments' : key;
                    
                    let formattedVal = String(val);
                    
                    // Format traits as bullet points
                    if (key !== 'Notes' && formattedVal.includes('\\n')) {
                        const lines = formattedVal.split('\\n').map(l => l.trim().replace(/^[-*•]\\s*/, '')).filter(l => l.length > 0);
                        const uniqueLines = [...new Set(lines)]; // Simple deduplication
                        if (uniqueLines.length > 1) {
                            formattedVal = '<ul style="margin:0; padding-left:20px; display:flex; flex-direction:column; gap:8px;">' + uniqueLines.map(l => `<li>${l}</li>`).join('') + '</ul>';
                        } else {
                            formattedVal = uniqueLines[0] || '';
                        }
                    } else {
                        // For Notes and simple strings: render linebreaks and bold text
                        formattedVal = formattedVal.replace(/\\n/g, '<br>');
                        formattedVal = formattedVal.replace(/\\*\\*(.*?)\\*\\*/g, '<strong style="color:var(--color-text-primary);">$1</strong>');
                    }
                    
                    const item = document.createElement('div');
                    item.innerHTML = `
                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted); margin-bottom:8px;">${displayKey}</div>
                        <div style="font-size:14px; color:rgba(255,255,255,0.85); line-height:1.6;">${formattedVal}</div>
                    `;
                    detailsContainer.appendChild(item);
                }
            }
            
            // Add view full page link at the bottom of scroll area if it's a character
            // (Assuming we might want to link to character edit page later, for now just a spacer)
            const spacer = document.createElement('div');
            spacer.style.height = '10px';
            detailsContainer.appendChild(spacer);
            
            this.modal.classList.add('nwp-modal-overlay--active');
        } catch (e) {
            console.error('Error parsing entity data for modal:', e);
        }
    },
    
    close() {
        if (this.modal) {
            this.modal.classList.remove('nwp-modal-overlay--active');
        }
    }
};

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('wiki-modal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                WikiModal.close();
            }
        });
    }
});
</script>
