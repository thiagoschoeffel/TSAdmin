<div class="modal hidden" data-modal="confirmation" data-confirm-modal role="dialog" aria-modal="true"
     aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message" aria-hidden="true" hidden>
    <div class="modal__backdrop" data-modal-backdrop data-confirm-backdrop></div>

    <div class="modal__panel modal__panel--sm" role="document">
        <button type="button" class="modal__close" data-modal-close aria-label="Fechar modal">
            <x-heroicon name="x-mark" class="h-5 w-5" />
        </button>

        <div class="modal__header text-center">
            <h2 class="modal__title" id="confirm-modal-title" data-confirm-title>Confirmar ação</h2>
            <p class="modal__description" id="confirm-modal-message" data-confirm-message>
                Tem certeza que deseja continuar?
            </p>
        </div>

        <div class="modal__actions">
            <button type="button" class="btn-ghost" data-confirm-cancel>Cancelar</button>
            <button type="button" class="modal__confirm btn-danger" data-confirm-confirm>
                Confirmar
            </button>
        </div>
    </div>
</div>
