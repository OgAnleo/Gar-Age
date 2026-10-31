import CustomerController from '@/actions/App/Http/Controllers/Tenant/CustomerController';
import CustomerForm from '@/components/customers/customer-form';
import FormPage from '@/components/form-page';

export default function CustomersCreate() {
    return (
        <FormPage title="Nuevo cliente">
            <CustomerForm
                action={CustomerController.store.form()}
                submitLabel="Crear cliente"
            />
        </FormPage>
    );
}
