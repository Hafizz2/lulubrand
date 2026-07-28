import React, { useState, useRef, useEffect } from 'react';

export default function ImageColorPickerModal({ imageUrl, isOpen, onClose, onColorSelected }) {
    const [sampledColor, setSampledColor] = useState('#8C6554');
    const [colorName, setColorName] = useState('');
    const [loupePos, setLoupePos] = useState({ x: 0, y: 0, visible: false });
    const [isLocked, setIsLocked] = useState(false);
    const imgRef = useRef(null);
    const canvasRef = useRef(null);

    useEffect(() => {
        if (!isOpen || !imageUrl) return;
        setIsLocked(false);
        setLoupePos({ x: 0, y: 0, visible: false });

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = imageUrl;

        img.onload = () => {
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            ctx.drawImage(img, 0, 0);
            canvasRef.current = { canvas, ctx, width: img.naturalWidth, height: img.naturalHeight };
        };
    }, [isOpen, imageUrl]);

    if (!isOpen || !imageUrl) return null;

    const sampleColorAtPoint = (clientX, clientY) => {
        if (!imgRef.current || !canvasRef.current) return;

        const rect = imgRef.current.getBoundingClientRect();
        const relX = clientX - rect.left;
        const relY = clientY - rect.top;

        if (relX < 0 || relX > rect.width || relY < 0 || relY > rect.height) {
            return;
        }

        const scaleX = canvasRef.current.width / rect.width;
        const scaleY = canvasRef.current.height / rect.height;

        const canvasX = Math.floor(relX * scaleX);
        const canvasY = Math.floor(relY * scaleY);

        try {
            const pixel = canvasRef.current.ctx.getImageData(canvasX, canvasY, 1, 1).data;
            const hex = "#" + ((1 << 24) + (pixel[0] << 16) + (pixel[1] << 8) + pixel[2]).toString(16).slice(1).toUpperCase();
            setSampledColor(hex);
            setLoupePos({
                x: relX,
                y: relY,
                visible: true
            });
        } catch (err) {
            console.error('Error reading canvas pixel:', err);
        }
    };

    const handlePointerMove = (clientX, clientY) => {
        if (isLocked) return; // Do not overwrite when locked!
        sampleColorAtPoint(clientX, clientY);
    };

    const handlePointerClick = (clientX, clientY) => {
        sampleColorAtPoint(clientX, clientY);
        setIsLocked(true); // Lock the sampled color on click/tap!
    };

    const handleTouchStart = (e) => {
        if (e.touches && e.touches[0]) {
            handlePointerClick(e.touches[0].clientX, e.touches[0].clientY);
        }
    };

    const handleSelectColor = () => {
        const finalName = colorName.trim() || `Color ${sampledColor}`;
        onColorSelected({
            color_code: sampledColor,
            value: finalName
        });
        onClose();
    };

    return (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-stone-950/80 backdrop-blur-md animate-in fade-in duration-150">
            <div className="bg-white max-w-2xl w-full rounded-3xl border border-[#E6DFD5] shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">

                {/* Modal Header */}
                <div className="px-5 py-4 border-b border-[#E6DFD5] flex items-center justify-between bg-[#FAF8F5]">
                    <div>
                        <span className="text-[10px] font-bold uppercase tracking-[0.2em] text-[#8C6554] block">
                            Interactive Fabric Eyedropper
                        </span>
                        <h3 className="text-xs sm:text-sm font-serif font-bold uppercase tracking-wider text-[#221F1F]">
                            Sample Color from Product Image
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

                {/* Instruction & Status Bar */}
                <div className={`px-4 sm:px-6 py-2.5 text-[10px] sm:text-[11px] font-bold uppercase transition-colors flex items-center justify-between border-b ${isLocked ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-[#8C6554]/10 text-[#8C6554] border-[#8C6554]/20'}`}>
                    <span className="flex items-center space-x-1.5">
                        {isLocked ? (
                            <>
                                <svg className="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Color Locked! Tap image again to unlock and pick another spot.</span>
                            </>
                        ) : (
                            <>
                                <svg className="w-4 h-4 text-[#8C6554]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                                <span>Hover/drag to preview, TAP/CLICK to LOCK color spot</span>
                            </>
                        )}
                    </span>
                    <div className="flex items-center space-x-2">
                        {isLocked && (
                            <button
                                type="button"
                                onClick={() => setIsLocked(false)}
                                className="text-[9px] font-bold uppercase text-stone-600 underline hover:text-[#221F1F]"
                            >
                                Unlock
                            </button>
                        )}
                        <span className="font-mono text-stone-900 bg-white px-2.5 py-0.5 rounded-full border border-[#E6DFD5] shadow-xs">
                            {sampledColor}
                        </span>
                    </div>
                </div>

                {/* Image Sampling Workspace Area */}
                <div className="flex-1 p-3 sm:p-4 overflow-auto flex items-center justify-center bg-stone-900 relative">
                    <div
                        className="relative cursor-crosshair select-none touch-none inline-block max-w-full"
                        onTouchStart={handleTouchStart}
                        onTouchMove={(e) => {
                            if (!isLocked && e.touches && e.touches[0]) {
                                handlePointerMove(e.touches[0].clientX, e.touches[0].clientY);
                            }
                        }}
                        onMouseMove={(e) => handlePointerMove(e.clientX, e.clientY)}
                        onClick={(e) => handlePointerClick(e.clientX, e.clientY)}
                    >
                        <img
                            ref={imgRef}
                            src={imageUrl}
                            alt="Sample Fabric Color"
                            className="max-h-[50vh] w-auto object-contain rounded-xl shadow-xl"
                        />

                        {/* Floating Loupe Indicator */}
                        {loupePos.visible && (
                            <div
                                className={`absolute pointer-events-none w-14 h-14 rounded-full border-4 shadow-2xl transform -translate-x-1/2 -translate-y-1/2 flex items-center justify-center transition-all duration-75 ${isLocked ? 'border-emerald-400 ring-4 ring-emerald-400/40 scale-110' : 'border-white'}`}
                                style={{
                                    left: `${loupePos.x}px`,
                                    top: `${loupePos.y}px`,
                                    backgroundColor: sampledColor
                                }}
                            >
                                {isLocked ? (
                                    <span className="text-white text-xs font-bold font-mono">✓</span>
                                ) : (
                                    <span className="w-2.5 h-2.5 rounded-full bg-white shadow-md"></span>
                                )}
                            </div>
                        )}
                    </div>
                </div>

                {/* Bottom Control Bar */}
                <div className="p-4 sm:p-6 bg-[#FAF8F5] border-t border-[#E6DFD5] space-y-3 sm:space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                        <div className="flex items-center space-x-3 bg-white p-2.5 rounded-2xl border border-[#E6DFD5] shadow-xs">
                            <div
                                className="w-9 h-9 rounded-full border border-stone-300 shadow-sm flex-shrink-0"
                                style={{ backgroundColor: sampledColor }}
                            ></div>
                            <div>
                                <span className="text-[9px] font-bold uppercase tracking-wider text-stone-400 block">Extracted Hex</span>
                                <input
                                    type="text"
                                    value={sampledColor}
                                    onChange={(e) => setSampledColor(e.target.value.toUpperCase())}
                                    className="text-xs font-mono font-bold text-[#221F1F] bg-transparent uppercase border-none p-0 focus:outline-none focus:ring-0"
                                />
                            </div>
                        </div>

                        <div className="sm:col-span-2">
                            <label className="block text-[10px] font-bold uppercase tracking-wider text-stone-600 mb-1">
                                Color Name (e.g. Midnight Black, Ivory, Satin Red)
                            </label>
                            <input
                                type="text"
                                value={colorName}
                                onChange={(e) => setColorName(e.target.value)}
                                placeholder="Enter custom color name..."
                                className="w-full px-4 py-2.5 bg-white border border-[#E6DFD5] rounded-xl text-xs font-medium text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                    </div>

                    <div className="flex justify-end space-x-3 pt-1">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-5 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-full text-xs font-bold uppercase tracking-wider min-h-[44px]"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            onClick={handleSelectColor}
                            className="px-6 py-3 bg-[#8C6554] hover:bg-[#755243] text-white rounded-full text-xs font-bold uppercase tracking-wider shadow-md flex items-center space-x-2 min-h-[44px]"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Save Color Swatch ({sampledColor})</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    );
}
