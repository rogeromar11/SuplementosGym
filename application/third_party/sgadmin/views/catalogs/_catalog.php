<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Plantilla generica de catalogo.
 * Espera: $table (objeto con name), $columns, $formFields, $saveEndpoint,
 *         $deleteEndpoint (usa {id}), $emptyMessage.
 */
// JSON para el bloque <script>: NO debe pasar por htmlspecialchars (las entidades
// no se decodifican dentro de <script> y rompen el JS). Se escapa "</" por seguridad.
$jsonColumns = str_replace('</', '<\\/', json_encode($columns, JSON_UNESCAPED_UNICODE));
$jsonFields = str_replace('</', '<\\/', json_encode($formFields, JSON_UNESCAPED_UNICODE));
?>
<div class="sg-page-header">
    <div>
        <h1><?php echo html_escape($table->name); ?></h1>
        <p><?php echo isset($table->description) ? html_escape($table->description) : ''; ?></p>
    </div>
    <div>
        <?php if (!isset($canAdd) || $canAdd): ?>
        <button type="button" class="btn btn-brand btn-add-item"><i class="bi bi-plus-lg me-1"></i><?php echo isset($table->addLabel) ? html_escape($table->addLabel) : 'Nuevo'; ?></button>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($afterHeaderView)): ?>
    <?php $this->load->view($afterHeaderView, get_defined_vars()); ?>
<?php endif; ?>

<div class="card sg-card">
    <div class="sg-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle data-table" data-lang-url="<?php echo base_url('assets/vendor/datatables/lang/es-ES.json'); ?>">
                <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?php echo html_escape($col['label']); ?></th>
                        <?php endforeach; ?>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr class="catalog-row"
                            data-json="<?php echo htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>">
                            <?php foreach ($columns as $col): ?>
                                <td><?php echo $this->load->view('catalogs/_cell', array('item' => $item, 'col' => $col), true); ?></td>
                            <?php endforeach; ?>
                            <td class="text-end">
                                <?php if (!isset($canEdit) || $canEdit): ?>
                                <button type="button" class="btn btn-sm btn-outline-brand btn-edit-item" title="Editar"><i class="bi bi-pencil"></i></button>
                                <?php endif; ?>
                                <?php if (!isset($canDelete) || $canDelete): ?>
                                <?php
                                    $canDeleteThis = !isset($deleteOnlyInactive) || (int)$item->is_active === 0;
                                    $deleteTitle = $canDeleteThis ? 'Eliminar' : 'Solo se pueden eliminar clientes inactivos';
                                ?>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-item" data-url="<?php echo str_replace('{id}', $item->id, $deleteEndpoint); ?>" title="<?php echo html_escape($deleteTitle); ?>" <?php echo $canDeleteThis ? '' : 'disabled'; ?>><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="catalogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-black text-white">
                <h5 class="modal-title"><span id="catalogModalTitle">Nuevo</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="catalogForm">
                    <input type="hidden" name="id" id="catalogId" value="">
                    <div class="row g-3" id="catalogFields"></div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.sgmsCatalog = {
    columns: <?php echo $jsonColumns; ?>,
    fields: <?php echo $jsonFields; ?>,
    saveEndpoint: '<?php echo $saveEndpoint; ?>',
    emptyMessage: <?php echo json_encode($emptyMessage ?? 'Sin registros.'); ?>
};
</script>
