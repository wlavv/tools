# ModuleHealth

LSG structural health scanner for WebTools Manager modules.

## Purpose

ModuleHealth audits `Modules/*` and compares each module against configurable profiles:

- required components
- recommended components
- optional components

It identifies:

- broken modules
- incomplete modules
- functional v1 modules
- enhanced modules
- missing required components
- useful upgrade opportunities

## Install

1. Copy `ModuleHealth` into `Modules/ModuleHealth`.
2. Make sure the module loader reads `module.json`.
3. Run migrations.
4. Clear config/routes/views if needed.
5. Access `/module-health`.

## Notes

This version intentionally has no external package dependencies beyond Laravel and your existing module loader.
