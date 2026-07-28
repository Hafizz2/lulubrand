import React, { useState, useRef, useEffect } from 'react';

export default function InteractiveHeroCropModal({ isOpen, imageUrl, mode, initialFocal, onClose, onSave }) {
    const [cropPos, setCropPos] = useState({ x: 50, y: 50 }); // percentages 0-100
    const [isDragging, setIsDragging] = useState(false);
    const containerRef = useRef(null);
    const imgRef = useRef(null);

    useEffect(() => {
        if (!isOpen || !initialFocal) return;
        // Parse initial focal e.g. "50% 30%" or "center top"
        let defaultX = 50;
        let defaultY = 50;

        if (initialFocal.includes('%')) {
            const parts = initialFocal.split(' ');
            if (parts.length === 2) {
                defaultX = parseFloat(parts[0]) || 50;
                defaultY = parseFloat(parts[1]) || 50;
            }
        } else {
            if (initialFocal.includes('left')) defaultX = 20;
            if (initialFocal.includes('right')) defaultX = 80;
            if (initialFocal.includes('top')) defaultY = 20;
            if (initialFocal.includes('bottom')) defaultY = 80;
        }

        setCropPos({ x: defaultX, y: defaultY });
    }, [isOpen, initialFocal, imageUrl]);

    if (!isOpen || !imageUrl) return null;

    const isDesktop = mode === 'desktop';
    // Aspect ratio aspect-[16/9] for desktop vs aspect-[9/16] for mobile
    const targetAspect = isDesktop ? (16 / 9) : (9 / 16);
    const deviceLabel = isDesktop ? '🖥️ Desktop (Wide 16:9 Viewport)' : '📱 Mobile (Portrait 9:16 Viewport)';

    const updatePositionFromClient = (clientX, clientY) => {
        if (!containerRef.current) return;
        const rect = containerRef.current.getBoundingClientRect();
        
        let xRel = ((clientX - rect.left) / rect.width) * 100;
        let yRel = ((clientY - rect.top) / rect.height) * 100;

        // Clamp between 0% and 100%
        xRel = Math.max(0, Math.min(100, Math.round(xRel)));
        yRel = Math.max(0, Math.min(100, Math.round(yRel)));

        setCropPos({ x: xRel, y: yRel });
    };

    const handleMouseDown = (e) => {
        setIsDragging(true);
        updatePositionFromClient(e.clientX, e.clientY);
    };

    const handleMouseMove = (e) => {
        if (isDragging) {
            updatePositionFromClient(e.clientX, e.clientY);
        }
    };

    const handleMouseUp = () => {
        setIsDragging(false);
    };

    const handleTouchMove = (e) => {
        if (e.touches && e.touches[0]) {
            updatePositionFromClient(e.touches[0].clientX, e.touches[0].clientY);
        }
    };

    const focalString = `${cropPos.x}% ${cropPos.y}%`;

    const handleSave = () => {
        onSave(focalString);
        onClose();
    };

    return (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-stone-950/80 backdrop-blur-md animate-in fade-in duration-150">
            <div className="bg-white max-w-4xl w-full rounded-3xl border border-[#E6DFD5] shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">

                {/* Modal Header */}
                <div className="px-6 py-4 border-b border-[#E6DFD5] flex items-center justify-between bg-[#FAF8F5]">
                    <div>
                        <span className="text-[10px] font-bold uppercase tracking-[0.2em] text-[#8C6554] block">
                            Interactive Visual Hero Crop Tool
                        </span>
                        <h3 className="text-sm font-serif font-bold uppercase tracking-wider text-[#221F1F]">
                            Position Crop Frame for {deviceLabel}
                        </h3>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="text-stone-400 hover:text-[#221F1F] text-2xl font-bold w-9 h-9 flex items-center justify-center"
                    >
                        &times;
                    </button>
                </div>

                {/* Instruction Bar */}
                <div className="px-6 py-2.5 bg-[#8C6554]/10 text-[#8C6554] text-[11px] font-bold uppercase tracking-wider border-b border-[#8C6554]/20 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
                        </svg>
                        <span>DRAG OR CLICK frame on image to choose exact {isDesktop ? 'Desktop' : 'Mobile'} focus area!</span>
                    </span>
                    <span className="font-mono bg-white text-stone-900 px-3 py-0.5 rounded-full border border-[#E6DFD5] shadow-xs">
                        Focal Pos: {focalString}
                    </span>
                </div>

                {/* Main Interactive Workspace (Image + Draggable Crop Box) */}
                <div className="flex-1 p-4 overflow-auto bg-stone-950 flex flex-col md:flex-row gap-6 items-center justify-center select-none">
                    
                    {/* Image Canvas Container */}
                    <div 
                        ref={containerRef}
                        onMouseDown={handleMouseDown}
                        onMouseMove={handleMouseMove}
                        onMouseUp={handleMouseUp}
                        onMouseLeave={handleMouseUp}
                        onTouchStart={(e) => { if (e.touches && e.touches[0]) updatePositionFromClient(e.touches[0].clientX, e.touches[0].clientY); }}
                        onTouchMove={handleTouchMove}
                        className="relative cursor-crosshair inline-block max-h-[55vh] max-w-full rounded-xl overflow-hidden shadow-2xl border-2 border-stone-800"
                    >
                        <img 
                            ref={imgRef}
                            src={imageUrl} 
                            alt="Crop Target" 
                            className="max-h-[55vh] w-auto object-contain block pointer-events-none" 
                        />

                        {/* Semi-transparent dark overlay */}
                        <div className="absolute inset-0 bg-black/40 pointer-events-none"></div>

                        {/* Locked Aspect Ratio Draggable Crop Lens Indicator */}
                        <div 
                            className="absolute pointer-events-none border-2 border-amber-400 ring-4 ring-amber-400/30 shadow-2xl transform -translate-x-1/2 -translate-y-1/2 flex flex-col items-center justify-between p-2 rounded-sm transition-all duration-75"
                            style={{
                                left: `${cropPos.x}%`,
                                top: `${cropPos.y}%`,
                                width: isDesktop ? '60%' : '35%',
                                height: isDesktop ? '35%' : '60%',
                                boxShadow: '0 0 0 9999px rgba(0, 0, 0, 0.55)'
                            }}
                        >
                            {/* Grid Rule of Thirds Guide */}
                            <div className="w-full h-full border border-dashed border-amber-300/60 grid grid-cols-3 grid-rows-3 relative">
                                <div className="col-span-3 border-b border-dashed border-amber-300/30"></div>
                                <div className="col-span-3 border-b border-dashed border-amber-300/30"></div>
                            </div>

                            <span className="bg-amber-400 text-stone-950 font-bold font-mono text-[9px] uppercase px-2 py-0.5 rounded shadow-md mt-1">
                                {isDesktop ? 'DESKTOP 16:9 CROP' : 'MOBILE 9:16 CROP'}
                            </span>
                        </div>
                    </div>

                    {/* Live Result Device Preview */}
                    <div className="flex flex-col items-center space-y-2 bg-stone-900 p-4 rounded-2xl border border-stone-800 shadow-xl flex-shrink-0">
                        <span className="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] block">
                            Live {isDesktop ? 'Desktop (16:9)' : 'Mobile (9:16)'} Preview
                        </span>
                        
                        <div 
                            className={`relative overflow-hidden bg-black rounded-lg shadow-2xl border-2 border-amber-400/50 ${isDesktop ? 'w-64 h-36' : 'w-36 h-64'}`}
                        >
                            <img 
                                src={imageUrl} 
                                alt="Live Device Preview" 
                                style={{ objectPosition: focalString }}
                                className="w-full h-full object-cover"
                            />
                            <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent p-3 flex flex-col justify-end">
                                <span className="text-white text-[10px] font-serif font-bold uppercase truncate">
                                    Preview Title
                                </span>
                            </div>
                        </div>

                        <span className="text-[9px] font-mono text-stone-400 mt-1">
                            Position: {focalString}
                        </span>
                    </div>

                </div>

                {/* Bottom Action Footer */}
                <div className="p-4 sm:p-6 bg-[#FAF8F5] border-t border-[#E6DFD5] flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <span className="text-xs font-bold uppercase text-stone-600">Quick Presets:</span>
                        <button type="button" onClick={() => setCropPos({ x: 50, y: 20 })} class="px-2.5 py-1 bg-white border border-[#E6DFD5] hover:border-[#8C6554] text-[10px] font-bold uppercase rounded-md">Top / Bust</button>
                        <button type="button" onClick={() => setCropPos({ x: 50, y: 50 })} class="px-2.5 py-1 bg-white border border-[#E6DFD5] hover:border-[#8C6554] text-[10px] font-bold uppercase rounded-md">Center</button>
                        <button type="button" onClick={() => setCropPos({ x: 50, y: 80 })} class="px-2.5 py-1 bg-white border border-[#E6DFD5] hover:border-[#8C6554] text-[10px] font-bold uppercase rounded-md">Bottom / Dress</button>
                    </div>

                    <div className="flex items-center space-x-3">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-5 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-full text-xs font-bold uppercase tracking-wider"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            onClick={handleSave}
                            className="px-6 py-3 bg-[#8C6554] hover:bg-[#755243] text-white rounded-full text-xs font-bold uppercase tracking-wider shadow-md flex items-center space-x-2"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Save {isDesktop ? 'Desktop' : 'Mobile'} Crop Position ({focalString})</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    );
}
