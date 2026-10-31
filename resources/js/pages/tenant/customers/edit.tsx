import CustomerController from '@/actions/App/Http/Controllers/Tenant/CustomerController';
import CustomerForm, {
    type CustomerFormValues,
} from '@/components/customers/customer-form';
import FormPage from '@/components/form-page';

export default function CustomersEdit({
    customer,
}: {
    customer: CustomerFormValues & { id: number };
}) {
    return (
        <FormPage title={`Editar ${customer.name}`}>
            <CustomerForm
                action={CustomerController.update.form({
                    customer: customer.id,
                })}
                defaults={customer}
                submitLabel="Guardar cambios"
            />
        </FormPage>
    );
}
